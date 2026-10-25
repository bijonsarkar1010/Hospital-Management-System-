<?php

namespace Tests\Feature;

use App\Models\Specialty;
use App\Models\SymptomRule;
use App\Services\SymptomSpecialtyPredictionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymptomSpecialtyPredictionTest extends TestCase
{
    use RefreshDatabase;

    protected SymptomSpecialtyPredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->service = app(SymptomSpecialtyPredictionService::class);
    }

    public function test_exact_skin_rash_predicts_dermatology()
    {
        $result = $this->service->predict('Patient has a severe skin rash with itching');

        $this->assertTrue($result['success']);
        $this->assertEquals('Dermatology', $result['suggested_specialty_name']);
        $this->assertFalse($result['is_urgent']);
        $this->assertStringContainsString('Dermatology', $result['explanation']);
    }

    public function test_palpitations_predicts_cardiology()
    {
        $result = $this->service->predict('Rapid heartbeat and palpitations during workout');

        $this->assertTrue($result['success']);
        $this->assertEquals('Cardiology', $result['suggested_specialty_name']);
        $this->assertFalse($result['is_urgent']);
    }

    public function test_joint_pain_predicts_orthopaedics()
    {
        $result = $this->service->predict('Chronic joint pain in knee and morning stiffness');

        $this->assertTrue($result['success']);
        $this->assertEquals('Orthopaedics', $result['suggested_specialty_name']);
    }

    public function test_persistent_cough_predicts_pulmonology()
    {
        $result = $this->service->predict('Persistent cough and wheezing for three weeks');

        $this->assertTrue($result['success']);
        $this->assertEquals('Pulmonology', $result['suggested_specialty_name']);
    }

    public function test_ear_pain_predicts_ent()
    {
        $result = $this->service->predict('Severe ear pain and ringing tinnitus in right ear');

        $this->assertTrue($result['success']);
        $this->assertEquals('ENT', $result['suggested_specialty_name']);
    }

    public function test_blurred_vision_predicts_ophthalmology()
    {
        $result = $this->service->predict('Blurred vision and heavy eye strain when reading');

        $this->assertTrue($result['success']);
        $this->assertEquals('Ophthalmology', $result['suggested_specialty_name']);
    }

    public function test_urgent_chest_pain_triggers_red_flag_alert()
    {
        $result = $this->service->predict('Severe chest pain with breathlessness and radiating arm pain');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_urgent']);
        $this->assertEquals('CRITICAL', $result['urgency_level']);
        $this->assertNotNull($result['urgent_instructions']);
        $this->assertStringContainsString('CRITICAL EMERGENCY WARNING', $result['urgent_instructions']);
    }

    public function test_acute_stroke_signs_trigger_urgency()
    {
        $result = $this->service->predict('Sudden facial droop and slurred speech');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_urgent']);
        $this->assertEquals('CRITICAL', $result['urgency_level']);
    }

    public function test_unknown_symptom_falls_back_to_general_medicine()
    {
        $result = $this->service->predict('Xyzqwerty obscure description with no medical keywords');

        $this->assertTrue($result['success']);
        $this->assertEquals('General Medicine', $result['suggested_specialty_name']);
        $this->assertEquals('Unknown', $result['confidence']);
        $this->assertFalse($result['is_urgent']);
    }
}
