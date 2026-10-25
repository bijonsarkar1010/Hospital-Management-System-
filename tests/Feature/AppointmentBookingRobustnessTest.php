<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingRobustnessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Specialty $cardiology;
    protected Specialty $dermatology;
    protected Doctor $doctorCardio;
    protected Doctor $doctorDerma;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        ClinicSetting::set('clinic_open_time', '08:00');
        ClinicSetting::set('clinic_close_time', '20:00');
        ClinicSetting::set('booking_horizon_days', '14');

        $this->cardiology = Specialty::create(['name' => 'Cardiology', 'slug' => 'cardiology']);
        $this->dermatology = Specialty::create(['name' => 'Dermatology', 'slug' => 'dermatology']);

        $this->doctorCardio = Doctor::create([
            'doctor_code' => 'DOC-CARD-1',
            'specialty_id' => $this->cardiology->id,
            'name' => 'Dr. Cardio Star',
            'email' => 'cardio@clinic.com',
            'phone' => '+15550001',
            'qualifications' => 'MD',
            'consultation_room' => 'Room 201',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        $this->doctorDerma = Doctor::create([
            'doctor_code' => 'DOC-DERM-1',
            'specialty_id' => $this->dermatology->id,
            'name' => 'Dr. Skin Expert',
            'email' => 'derma@clinic.com',
            'phone' => '+15550002',
            'qualifications' => 'MD',
            'consultation_room' => 'Room 202',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        for ($d = 0; $d <= 6; $d++) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctorCardio->id,
                'day_of_week' => $d,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);

            DoctorSchedule::create([
                'doctor_id' => $this->doctorDerma->id,
                'day_of_week' => $d,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);
        }

        \App\Models\SymptomRule::create([
            'symptom_entity' => 'Skin rash',
            'intent' => 'Dermatological issue',
            'specialty_id' => $this->dermatology->id,
            'keywords' => ['rash', 'itchy skin', 'itch'],
            'synonyms' => ['eczema', 'dermatitis'],
            'priority' => 10,
            'is_urgent' => false,
            'is_active' => true,
        ]);

        $this->patient = Patient::create([
            'patient_code' => 'PAT-TEST-001',
            'name' => 'Jonathan Health',
            'age' => 35,
            'sex' => 'Male',
            'phone' => '+1 (555) 123-4567',
            'address' => '789 Medical Ave',
        ]);
    }

    public function test_booking_for_today_with_past_time_searches_forward_instead_of_failing(): void
    {
        // Request time is 06:00 AM today (in the past, before clinic open)
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Heart palpitations and rapid pulse',
            'requested_date' => now()->toDateString(),
            'requested_time' => '06:00',
            'booking_mode' => 'auto',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));

        $pending = session('pending_allocation');
        $this->assertNotNull($pending);
        $this->assertTrue($pending['is_alternative_slot']);
        $this->assertEquals($this->doctorCardio->id, $pending['doctor_id']);
        $this->assertNotNull($pending['assigned_time']);
        $this->assertStringContainsString('past or unavailable', $pending['message']);
    }

    public function test_option_a_auto_mode_records_auto_booking_mode(): void
    {
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Severe rash with itchy skin',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'auto',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));

        $pending = session('pending_allocation');
        $this->assertEquals('auto', $pending['booking_mode']);
        $this->assertEquals($this->doctorDerma->id, $pending['doctor_id']);

        // Confirm booking
        $this->actingAs($this->admin)->post('/appointments/confirm');

        $appointment = Appointment::where('patient_id', $this->patient->id)->latest()->first();
        $this->assertEquals('auto', $appointment->booking_mode);
        $this->assertEquals($this->doctorDerma->id, $appointment->doctor_id);
    }

    public function test_option_b_requires_specialty_selection(): void
    {
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Chest pain',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'specialty',
            'preferred_specialty_id' => '', // omitted
        ]);

        $response->assertSessionHasErrors(['preferred_specialty_id']);
    }

    public function test_option_c_requires_doctor_selection(): void
    {
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Chest pain',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'doctor',
            'preferred_doctor_id' => '', // omitted
        ]);

        $response->assertSessionHasErrors(['preferred_doctor_id']);
    }

    public function test_requested_time_with_seconds_is_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Skin rash',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '11:30:00',
            'booking_mode' => 'auto',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));
        $pending = session('pending_allocation');
        $this->assertEquals('11:30:00', $pending['assigned_time']);
    }

    public function test_new_patient_registration_with_integrated_appointment_succeeds(): void
    {
        $response = $this->actingAs($this->admin)->post('/patients', [
            'name' => 'Clara Barton',
            'age' => 40,
            'sex' => 'Female',
            'phone' => '+1 (555) 777-8888',
            'address' => '123 Caring Way',
            'primary_complaint' => 'Heart palpitations and dizziness',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '09:00',
            'booking_mode' => 'auto',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));

        $this->assertDatabaseHas('patients', [
            'name' => 'Clara Barton',
            'phone' => '+1 (555) 777-8888',
        ]);

        $patient = Patient::where('phone', '+1 (555) 777-8888')->first();
        $pending = session('pending_allocation');
        $this->assertEquals($patient->id, $pending['patient_id']);
        $this->assertEquals($this->doctorCardio->id, $pending['doctor_id']);
    }

    public function test_confirm_booking_when_session_expired_redirects_with_error(): void
    {
        // No pending_allocation in session
        $response = $this->actingAs($this->admin)->post('/appointments/confirm');

        $response->assertRedirect(route('appointments.create'));
        $response->assertSessionHas('error');
    }
}
