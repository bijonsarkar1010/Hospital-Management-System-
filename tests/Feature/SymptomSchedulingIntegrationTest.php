<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentPrediction;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\SymptomRule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymptomSchedulingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@carepoint.com')->first();

        $this->patient = Patient::create([
            'patient_code' => 'PAT-INT-0001',
            'name' => 'Integration Patient',
            'age' => 32,
            'sex' => 'Female',
            'phone' => '+1 (555) 333-4444',
            'address' => '505 Health Plaza',
        ]);
    }

    public function test_booking_with_complaint_allocates_doctor_of_predicted_specialty_and_saves_prediction_record()
    {
        $dermatology = Specialty::where('name', 'Dermatology')->first();

        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Severe skin rash with red itchy lesions',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'auto',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));

        // Verify session pending_allocation has prediction data
        $pending = session('pending_allocation');
        $this->assertNotNull($pending);
        $this->assertEquals($dermatology->name, $pending['doctor_specialty']);
        $this->assertEquals($dermatology->id, $pending['prediction_data']['predicted_specialty_id']);
        $this->assertEquals($dermatology->id, $pending['prediction_data']['confirmed_specialty_id']);
        $this->assertFalse($pending['prediction_data']['is_override']);

        // Now confirm booking
        $confirmResponse = $this->actingAs($this->admin)->post('/appointments/confirm');
        $confirmResponse->assertRedirect();

        $appointment = Appointment::where('patient_id', $this->patient->id)->latest()->first();
        $this->assertNotNull($appointment);
        $this->assertEquals($dermatology->id, $appointment->doctor->specialty_id);

        // Verify AppointmentPrediction record was created and linked
        $prediction = AppointmentPrediction::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($prediction);
        $this->assertEquals($dermatology->id, $prediction->predicted_specialty_id);
        $this->assertEquals($dermatology->id, $prediction->confirmed_specialty_id);
        $this->assertFalse($prediction->is_override);
        $this->assertStringContainsString('Dermatology', $prediction->explanation);
    }

    public function test_specialty_override_is_recorded_when_receptionist_selects_different_specialty()
    {
        $cardiology = Specialty::where('name', 'Cardiology')->first();
        $dermatology = Specialty::where('name', 'Dermatology')->first();

        // Complaint is skin rash (Dermatology), but receptionist explicitly selects Cardiology
        $response = $this->actingAs($this->admin)->post('/appointments/allocate', [
            'patient_id' => $this->patient->id,
            'primary_complaint' => 'Skin rash with mild itching',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '10:00',
            'booking_mode' => 'specialty',
            'preferred_specialty_id' => $cardiology->id,
            'override_reason' => 'Patient specifically requested cardiologist consultation for related cardiovascular check',
        ]);

        $response->assertRedirect(route('appointments.confirm_preview'));

        $pending = session('pending_allocation');
        $this->assertEquals($cardiology->id, $pending['prediction_data']['confirmed_specialty_id']);
        $this->assertEquals($dermatology->id, $pending['prediction_data']['predicted_specialty_id']);
        $this->assertTrue($pending['prediction_data']['is_override']);

        // Confirm booking
        $this->actingAs($this->admin)->post('/appointments/confirm');

        $appointment = Appointment::where('patient_id', $this->patient->id)->latest()->first();
        $this->assertEquals($cardiology->id, $appointment->doctor->specialty_id);

        $prediction = AppointmentPrediction::where('appointment_id', $appointment->id)->first();
        $this->assertTrue($prediction->is_override);
        $this->assertEquals('Patient specifically requested cardiologist consultation for related cardiovascular check', $prediction->override_reason);
    }

    public function test_symptom_rules_can_be_created_updated_and_toggled()
    {
        $specialty = Specialty::where('name', 'Neurology')->first();

        // 1. Create rule
        $response = $this->actingAs($this->admin)->post('/settings/symptom-rules', [
            'symptom_entity' => 'Severe Cluster Migraine',
            'intent' => 'Neurological headache episode',
            'specialty_id' => $specialty->id,
            'keywords_input' => 'cluster migraine, sharp temple pain, unilateral eye tearing',
            'synonyms_input' => 'suicide headache, trigeminal neuralgia',
            'priority' => 35,
            'is_urgent' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/settings/symptom-rules');
        $this->assertDatabaseHas('symptom_rules', [
            'symptom_entity' => 'Severe Cluster Migraine',
            'specialty_id' => $specialty->id,
            'priority' => 35,
        ]);

        $rule = SymptomRule::where('symptom_entity', 'Severe Cluster Migraine')->first();

        // 2. Toggle active status
        $this->actingAs($this->admin)->post("/settings/symptom-rules/{$rule->id}/toggle");
        $rule->refresh();
        $this->assertFalse($rule->is_active);

        // 3. Test simulator endpoint
        $simResponse = $this->actingAs($this->admin)->postJson('/settings/symptom-rules/test-simulator', [
            'phrase' => 'Patient has severe earache and tinnitus ringing',
        ]);

        $simResponse->assertStatus(200);
        $simResponse->assertJson([
            'success' => true,
            'suggested_specialty_name' => 'ENT',
        ]);
    }

    public function test_api_symptoms_predict_endpoint_returns_json_prediction()
    {
        $response = $this->actingAs($this->admin)->postJson('/api/symptoms/predict', [
            'complaint' => 'Sudden facial droop and arm weakness',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_urgent' => true,
            'urgency_level' => 'CRITICAL',
        ]);
    }
}
