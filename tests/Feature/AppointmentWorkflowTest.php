<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\DoctorAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Doctor $doctor;
    protected Patient $patient;
    protected Specialty $specialty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();

        $this->specialty = Specialty::create([
            'name' => 'General Medicine',
            'slug' => 'general-medicine',
        ]);

        $this->doctor = Doctor::create([
            'doctor_code' => 'DOC-001',
            'specialty_id' => $this->specialty->id,
            'name' => 'Dr. Robert Harrison',
            'email' => 'dr.harrison@carepoint.com',
            'phone' => '+1 (555) 401-1001',
            'qualifications' => 'MD, FACP',
            'consultation_room' => 'Room 101',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        for ($i = 0; $i <= 6; $i++) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctor->id,
                'day_of_week' => $i,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
            ]);
        }

        $this->patient = Patient::create([
            'patient_code' => 'PAT-2026-0001',
            'name' => 'Emily Watson',
            'age' => 29,
            'sex' => 'Female',
            'phone' => '+1 (555) 987-1111',
            'address' => '456 Elm Street',
        ]);
    }

    public function test_existing_patients_can_be_found_by_mobile_or_patient_id(): void
    {
        // Search by ID
        $response1 = $this->actingAs($this->admin)->get('/patients?search=PAT-2026-0001');
        $response1->assertStatus(200);
        $response1->assertSee('Emily Watson');

        // Search by phone
        $response2 = $this->actingAs($this->admin)->get('/patients?search=987-1111');
        $response2->assertStatus(200);
        $response2->assertSee('PAT-2026-0001');
    }

    public function test_system_assigns_suitable_doctor_when_no_doctor_or_specialty_selected(): void
    {
        $service = app(AppointmentSchedulingService::class);

        $allocation = $service->findAllocation([
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'General checkup',
            'preferred_doctor_id' => null,
            'preferred_specialty_id' => null,
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
        ]);

        $this->assertTrue($allocation['success']);
        $this->assertEquals('auto', $allocation['booking_mode']);
        $this->assertEquals($this->doctor->id, $allocation['doctor']->id);
    }

    public function test_appointments_outside_working_hours_result_in_alternative_slot(): void
    {
        $service = app(AppointmentSchedulingService::class);

        // Request at 05:00 AM (doctor works 09:00 - 17:00)
        $allocation = $service->findAllocation([
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Early morning consultation',
            'preferred_doctor_id' => $this->doctor->id,
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '05:00',
        ]);

        // Engine must offer earliest valid working hour slot (09:00:00)
        $this->assertTrue($allocation['is_alternative_slot']);
        $this->assertEquals('09:00:00', $allocation['assigned_time']);
    }

    public function test_rescheduling_validates_new_slot_and_releases_old_slot(): void
    {
        $service = app(AppointmentSchedulingService::class);
        $testDate = now()->addDays(2)->toDateString();

        $apt = $service->bookAppointment([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'assigned_date' => $testDate,
            'assigned_time' => '10:00:00',
            'end_time' => '10:30:00',
            'primary_complaint' => 'Original',
        ]);

        $this->assertEquals(Appointment::STATUS_SCHEDULED, $apt->status);

        // Reschedule to 11:00:00
        $newApt = $service->reschedule($apt, $testDate, '11:00:00', $this->doctor->id);

        $this->assertEquals('11:00:00', $newApt->appointment_time);
        $this->assertEquals(Appointment::STATUS_SCHEDULED, $newApt->status);

        // Old appointment should be marked cancelled with reference
        $apt->refresh();
        $this->assertEquals(Appointment::STATUS_CANCELLED, $apt->status);
        $this->assertStringContainsString('Rescheduled', $apt->cancellation_reason);
    }

    public function test_dashboard_statistics_update_after_appointment_status_changes(): void
    {
        $today = now()->toDateString();

        $apt = Appointment::create([
            'appointment_code' => 'APT-DASH-001',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'requested_date' => $today,
            'requested_time' => '10:00:00',
            'appointment_date' => $today,
            'appointment_time' => '10:00:00',
            'end_time' => '10:30:00',
            'primary_complaint' => 'Fever',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('APT-DASH-001');

        // Update status to Completed
        $this->actingAs($this->admin)->patch("/appointments/{$apt->id}/status", [
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $apt->refresh();
        $this->assertEquals(Appointment::STATUS_COMPLETED, $apt->status);

        // Verify dashboard reflects completed count
        $responseAfter = $this->actingAs($this->admin)->get('/dashboard');
        $responseAfter->assertStatus(200);
        $responseAfter->assertSee('Completed');
    }

    public function test_appointment_slip_displays_correct_patient_doctor_and_time(): void
    {
        $apt = Appointment::create([
            'appointment_code' => 'APT-SLIP-777',
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'requested_date' => now()->toDateString(),
            'requested_time' => '14:00:00',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '14:00:00',
            'end_time' => '14:30:00',
            'primary_complaint' => 'Skin irritation',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $response = $this->actingAs($this->admin)->get("/appointments/{$apt->id}/slip");
        $response->assertStatus(200);
        $response->assertSee('APT-SLIP-777');
        $response->assertSee('Emily Watson');
        $response->assertSee('Dr. Robert Harrison');
        $response->assertSee('Room 101');
        $response->assertSee('Print Appointment Slip');
    }
}
