<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();

        $specialty = Specialty::create([
            'name' => 'General Medicine',
            'slug' => 'general-medicine',
        ]);

        $this->doctor = Doctor::create([
            'doctor_code' => 'DOC-001',
            'specialty_id' => $specialty->id,
            'name' => 'Dr. Robert Harrison',
            'email' => 'dr.harrison@carepoint.com',
            'phone' => '+1 (555) 019-1001',
            'qualifications' => 'MD, FACP',
            'consultation_room' => 'Room 101',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        // Schedule on all days
        for ($i = 0; $i <= 6; $i++) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctor->id,
                'day_of_week' => $i,
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);
        }
    }

    public function test_new_patient_can_be_registered_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post('/patients', [
            'name' => 'Johnathon Smith',
            'age' => 40,
            'sex' => 'Male',
            'phone' => '+1 (555) 987-6543',
            'address' => '123 Baker Street, Suite 4',
            'primary_complaint' => 'Mild fever and dry cough',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'auto',
            'blood_group' => 'O+',
            'diabetes_status' => 'Non-Diabetic',
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));
        $this->assertDatabaseHas('patients', [
            'name' => 'Johnathon Smith',
            'phone' => '+1 (555) 987-6543',
        ]);

        $patient = Patient::where('phone', '+1 (555) 987-6543')->first();
        $this->assertNotNull($patient->patient_code);
        $this->assertStringStartsWith('PAT-', $patient->patient_code);
    }

    public function test_duplicate_phone_check_identifies_existing_patient(): void
    {
        Patient::create([
            'patient_code' => 'PAT-2026-0001',
            'name' => 'Existing Patient',
            'age' => 30,
            'sex' => 'Female',
            'phone' => '+1 (555) 123-4567',
            'address' => '789 Oak Lane',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/patients/check-duplicate?phone=%2B1%20(555)%20123-4567');

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'patient' => [
                'name' => 'Existing Patient',
            ],
        ]);
    }

    public function test_patient_registration_fails_if_required_fields_missing(): void
    {
        $response = $this->actingAs($this->admin)->post('/patients', []);

        $response->assertSessionHasErrors(['name', 'sex', 'phone', 'address', 'primary_complaint', 'requested_date', 'requested_time']);
    }
}
