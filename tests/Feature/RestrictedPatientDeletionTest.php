<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientDeletionAudit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestrictedPatientDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $authorizedAdmin;
    protected User $receptionAdmin;
    protected Patient $patientWithActiveApt;
    protected Patient $eligiblePatient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Fetch seeded admin and assign system_admin role
        $this->authorizedAdmin = User::where('email', 'admin@carepoint.com')->first();
        $this->authorizedAdmin->update(['role' => 'system_admin']);

        // Seeded ordinary receptionist (role: reception_admin)
        $this->receptionAdmin = User::where('email', 'sarah.reception@carepoint.com')->first();

        $doctor = Doctor::first();

        // 1. Patient with active upcoming appointment
        $this->patientWithActiveApt = Patient::create([
            'patient_code' => 'PAT-TEST-0001',
            'name' => 'Active Booking Patient',
            'age' => 45,
            'sex' => 'Male',
            'phone' => '+1 (555) 000-0001',
            'address' => '101 Medical Way',
        ]);

        Appointment::create([
            'appointment_code' => 'APT-TEST-0001',
            'patient_id' => $this->patientWithActiveApt->id,
            'doctor_id' => $doctor->id,
            'requested_date' => now()->addDays(2)->toDateString(),
            'requested_time' => '10:00:00',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '10:00:00',
            'end_time' => '10:30:00',
            'primary_complaint' => 'Routine follow-up',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        // 2. Eligible Patient (no active bookings; only past completed appointment)
        $this->eligiblePatient = Patient::create([
            'patient_code' => 'PAT-TEST-0002',
            'name' => 'Historical Patient',
            'age' => 38,
            'sex' => 'Female',
            'phone' => '+1 (555) 000-0002',
            'address' => '202 Oak Avenue',
        ]);

        Appointment::create([
            'appointment_code' => 'APT-TEST-0002',
            'patient_id' => $this->eligiblePatient->id,
            'doctor_id' => $doctor->id,
            'requested_date' => now()->subDays(5)->toDateString(),
            'requested_time' => '09:00:00',
            'appointment_date' => now()->subDays(5)->toDateString(),
            'appointment_time' => '09:00:00',
            'end_time' => '09:30:00',
            'primary_complaint' => 'Previous checkup',
            'status' => Appointment::STATUS_COMPLETED,
        ]);
    }

    public function test_ordinary_receptionist_is_denied_access_to_patient_deletion_page()
    {
        $response = $this->actingAs($this->receptionAdmin)->get('/settings/restricted/patient-deletion');
        $response->assertStatus(403);
    }

    public function test_ordinary_receptionist_is_denied_from_posting_patient_deletion()
    {
        $response = $this->actingAs($this->receptionAdmin)->post('/settings/restricted/patient-deletion', [
            'patient_id' => $this->eligiblePatient->id,
            'confirm_patient_code' => $this->eligiblePatient->patient_code,
            'reason' => 'Unauthorized deletion attempt by reception staff',
            'acknowledge_irreversible' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('patients', ['id' => $this->eligiblePatient->id]);
    }

    public function test_authorized_admin_can_view_patient_deletion_page()
    {
        $response = $this->actingAs($this->authorizedAdmin)->get('/settings/restricted/patient-deletion');
        $response->assertStatus(200);
        $response->assertSee('Permanent Patient Deletion');
    }

    public function test_deletion_is_blocked_when_patient_has_active_appointments()
    {
        $response = $this->actingAs($this->authorizedAdmin)->post('/settings/restricted/patient-deletion', [
            'patient_id' => $this->patientWithActiveApt->id,
            'confirm_patient_code' => $this->patientWithActiveApt->patient_code,
            'reason' => 'Patient requested GDPR removal but has upcoming appointment',
            'acknowledge_irreversible' => 1,
        ]);

        $response->assertSessionHasErrors(['active_appointments']);
        $this->assertDatabaseHas('patients', ['id' => $this->patientWithActiveApt->id]);

        // Assert audit record was created documenting the rejected attempt
        $this->assertDatabaseHas('patient_deletion_audits', [
            'patient_code' => $this->patientWithActiveApt->patient_code,
            'outcome' => 'rejected_active_appointments',
        ]);
    }

    public function test_deletion_fails_if_confirmation_code_does_not_match()
    {
        $response = $this->actingAs($this->authorizedAdmin)->post('/settings/restricted/patient-deletion', [
            'patient_id' => $this->eligiblePatient->id,
            'confirm_patient_code' => 'WRONG-CODE',
            'reason' => 'Compliance test with mismatched ID',
            'acknowledge_irreversible' => 1,
        ]);

        $response->assertSessionHasErrors(['confirm_patient_code']);
        $this->assertDatabaseHas('patients', ['id' => $this->eligiblePatient->id]);
    }

    public function test_deletion_fails_if_reason_is_missing_or_too_short()
    {
        $response = $this->actingAs($this->authorizedAdmin)->post('/settings/restricted/patient-deletion', [
            'patient_id' => $this->eligiblePatient->id,
            'confirm_patient_code' => $this->eligiblePatient->patient_code,
            'reason' => 'Short',
            'acknowledge_irreversible' => 1,
        ]);

        $response->assertSessionHasErrors(['reason']);
        $this->assertDatabaseHas('patients', ['id' => $this->eligiblePatient->id]);
    }

    public function test_authorized_admin_can_successfully_delete_eligible_patient()
    {
        $patientId = $this->eligiblePatient->id;
        $patientCode = $this->eligiblePatient->patient_code;

        $response = $this->actingAs($this->authorizedAdmin)->post('/settings/restricted/patient-deletion', [
            'patient_id' => $patientId,
            'confirm_patient_code' => $patientCode,
            'reason' => 'Formal patient data removal request verified by legal team',
            'acknowledge_irreversible' => 1,
        ]);

        $response->assertRedirect('/settings/restricted/patient-deletion');
        $response->assertSessionHas('success');

        // Verify patient is permanently expunged
        $this->assertDatabaseMissing('patients', ['id' => $patientId]);

        // Verify independent audit log exists and preserves patient code and reason
        $this->assertDatabaseHas('patient_deletion_audits', [
            'patient_code' => $patientCode,
            'admin_id' => $this->authorizedAdmin->id,
            'outcome' => 'success',
        ]);
    }
}
