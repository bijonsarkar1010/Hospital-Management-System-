<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AppointmentSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected AppointmentSchedulingService $schedulingService;
    protected DoctorAvailabilityService $availabilityService;
    protected Doctor $doctorGeneral;
    protected Doctor $doctorCardio;
    protected Patient $patient1;
    protected Patient $patient2;
    protected Specialty $specGeneral;
    protected Specialty $specCardio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityService = new DoctorAvailabilityService();
        $this->schedulingService = new AppointmentSchedulingService($this->availabilityService);

        ClinicSetting::set('clinic_open_time', '08:00');
        ClinicSetting::set('clinic_close_time', '20:00');
        ClinicSetting::set('booking_horizon_days', '14');

        $this->specGeneral = Specialty::create(['name' => 'General Medicine', 'slug' => 'general-medicine']);
        $this->specCardio = Specialty::create(['name' => 'Cardiology', 'slug' => 'cardiology']);

        // Doctor 1: General Medicine (30 min duration, works 09:00 - 17:00, lunch break 13:00 - 14:00)
        $this->doctorGeneral = Doctor::create([
            'doctor_code' => 'DOC-101',
            'specialty_id' => $this->specGeneral->id,
            'name' => 'Dr. General Smith',
            'email' => 'smith@clinic.com',
            'phone' => '+1555101001',
            'qualifications' => 'MD',
            'consultation_room' => 'Room 101',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        for ($d = 0; $d <= 6; $d++) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctorGeneral->id,
                'day_of_week' => $d,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
            ]);
        }

        DoctorBreak::create([
            'doctor_id' => $this->doctorGeneral->id,
            'day_of_week' => null,
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'label' => 'Lunch Break',
        ]);

        // Doctor 2: Cardiology (30 min duration)
        $this->doctorCardio = Doctor::create([
            'doctor_code' => 'DOC-102',
            'specialty_id' => $this->specCardio->id,
            'name' => 'Dr. Cardio Heart',
            'email' => 'heart@clinic.com',
            'phone' => '+1555101002',
            'qualifications' => 'MD, FACC',
            'consultation_room' => 'Room 201',
            'consultation_duration' => 30,
            'is_active' => true,
        ]);

        for ($d = 0; $d <= 6; $d++) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctorCardio->id,
                'day_of_week' => $d,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
            ]);
        }

        $this->patient1 = Patient::create([
            'patient_code' => 'PAT-001',
            'name' => 'Alice Adams',
            'age' => 28,
            'sex' => 'Female',
            'phone' => '+1555000001',
            'address' => '100 Main St',
        ]);

        $this->patient2 = Patient::create([
            'patient_code' => 'PAT-002',
            'name' => 'Bob Brown',
            'age' => 45,
            'sex' => 'Male',
            'phone' => '+1555000002',
            'address' => '200 Market St',
        ]);
    }

    public function test_available_requested_slot_is_allocated_correctly(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient1->id,
            'primary_complaint' => 'Routine checkup',
            'preferred_doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '10:00',
        ]);

        $this->assertTrue($allocation['success']);
        $this->assertEquals($this->doctorGeneral->id, $allocation['doctor']->id);
        $this->assertEquals($testDate, $allocation['assigned_date']);
        $this->assertEquals('10:00:00', $allocation['assigned_time']);
        $this->assertFalse($allocation['is_alternative_slot']);
    }

    public function test_occupied_slot_results_in_next_available_slot_search(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        // Occupy 10:00 to 10:30 for doctorGeneral
        Appointment::create([
            'appointment_code' => 'APT-TEST-001',
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '10:00:00',
            'appointment_date' => $testDate,
            'appointment_time' => '10:00:00',
            'end_time' => '10:30:00',
            'primary_complaint' => 'Pre-booked slot',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        // Patient 2 requests 10:00 for the same doctor
        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient2->id,
            'primary_complaint' => 'Consultation',
            'preferred_doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '10:00',
        ]);

        $this->assertTrue($allocation['success']);
        $this->assertTrue($allocation['is_alternative_slot']);
        // Should allocate earliest next slot: 10:30:00
        $this->assertEquals('10:30:00', $allocation['assigned_time']);
    }

    public function test_doctor_on_leave_is_never_assigned_during_leave(): void
    {
        $leaveDate = now()->addDays(3)->toDateString();

        // Place doctor on leave
        DoctorUnavailability::create([
            'doctor_id' => $this->doctorGeneral->id,
            'start_date' => $leaveDate,
            'end_date' => $leaveDate,
            'type' => 'leave',
            'reason' => 'Family vacation',
        ]);

        // Attempt booking on leave date specifically for doctorGeneral
        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient1->id,
            'primary_complaint' => 'Checkup',
            'preferred_doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $leaveDate,
            'requested_time' => '10:00',
        ]);

        // Should not assign on leaveDate; should offer next available day
        $this->assertNotEquals($leaveDate, $allocation['assigned_date']);
        $this->assertTrue($allocation['is_alternative_slot']);
    }

    public function test_doctor_on_break_is_never_assigned_during_break(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        // Lunch break is 13:00 - 14:00. Request 13:00
        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient1->id,
            'primary_complaint' => 'Checkup',
            'preferred_doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '13:00',
        ]);

        // Cannot be during 13:00 - 14:00
        $this->assertTrue($allocation['is_alternative_slot']);
        $this->assertTrue($allocation['assigned_time'] >= '14:00:00' || $allocation['assigned_time'] < '13:00:00');
    }

    public function test_cancelled_appointment_frees_slot_for_future_allocation(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        // Create cancelled appointment at 11:00
        $apt = Appointment::create([
            'appointment_code' => 'APT-CANCEL-001',
            'patient_id' => $this->patient1->id,
            'doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '11:00:00',
            'appointment_date' => $testDate,
            'appointment_time' => '11:00:00',
            'end_time' => '11:30:00',
            'primary_complaint' => 'Old cancelled booking',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        // Patient 2 requests 11:00
        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient2->id,
            'primary_complaint' => 'New booking at 11:00',
            'preferred_doctor_id' => $this->doctorGeneral->id,
            'requested_date' => $testDate,
            'requested_time' => '11:00',
        ]);

        // Slot at 11:00 must be available because old appointment is Cancelled!
        $this->assertFalse($allocation['is_alternative_slot']);
        $this->assertEquals('11:00:00', $allocation['assigned_time']);
    }

    public function test_system_respects_selected_specialty(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $this->patient1->id,
            'primary_complaint' => 'Heart palpitations',
            'preferred_specialty_id' => $this->specCardio->id,
            'requested_date' => $testDate,
            'requested_time' => '10:00',
        ]);

        $this->assertEquals($this->doctorCardio->id, $allocation['doctor']->id);
        $this->assertEquals($this->specCardio->id, $allocation['doctor']->specialty_id);
    }

    public function test_concurrency_lock_prevents_double_booking(): void
    {
        $testDate = now()->addDays(2)->toDateString();

        $allocationData = [
            'doctor_id' => $this->doctorGeneral->id,
            'patient_id' => $this->patient1->id,
            'assigned_date' => $testDate,
            'assigned_time' => '11:00:00',
            'end_time' => '11:30:00',
            'requested_date' => $testDate,
            'requested_time' => '11:00:00',
            'primary_complaint' => 'Test',
        ];

        // First receptionist books successfully
        $apt1 = $this->schedulingService->bookAppointment($allocationData);
        $this->assertNotNull($apt1->id);

        // Second receptionist attempts to book the exact same slot concurrently
        $this->expectException(ValidationException::class);
        $this->schedulingService->bookAppointment([
            'doctor_id' => $this->doctorGeneral->id,
            'patient_id' => $this->patient2->id,
            'assigned_date' => $testDate,
            'assigned_time' => '11:00:00',
            'end_time' => '11:30:00',
            'requested_date' => $testDate,
            'requested_time' => '11:00:00',
            'primary_complaint' => 'Concurrent conflict attempt',
        ]);
    }
}
