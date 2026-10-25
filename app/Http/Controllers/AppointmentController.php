<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Services\AppointmentSchedulingService;
use App\Services\DoctorAvailabilityService;
use App\Services\SymptomSpecialtyPredictionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentSchedulingService $schedulingService,
        protected DoctorAvailabilityService $availabilityService,
        protected SymptomSpecialtyPredictionService $predictionService
    ) {}

    /**
     * Display all appointments with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.specialty', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('patient_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('doctor', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->input('doctor_id'));
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('doctor', function ($q) use ($request) {
                $q->where('specialty_id', $request->input('specialty_id'));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        $doctors = Doctor::active()->orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        $statuses = Appointment::getStatuses();

        return view('appointments.index', compact('appointments', 'doctors', 'specialties', 'statuses'));
    }

    /**
     * Show booking form for an existing or selected patient.
     */
    public function create(Request $request)
    {
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->input('patient_id'));
        }

        $patients = Patient::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        $doctors = Doctor::active()->with('specialty')->orderBy('name')->get();

        return view('appointments.create', compact('selectedPatient', 'patients', 'specialties', 'doctors'));
    }

    /**
     * Allocate slot using scheduling engine and redirect to confirmation preview.
     */
    public function allocate(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'primary_complaint' => ['required', 'string', 'max:500'],
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'requested_time' => ['required', 'date_format:H:i,H:i:s'],
            'booking_mode' => ['required', 'string', Rule::in(['auto', 'specialty', 'doctor'])],
            'preferred_specialty_id' => ['nullable', 'required_if:booking_mode,specialty', 'exists:specialties,id'],
            'preferred_doctor_id' => ['nullable', 'required_if:booking_mode,doctor', 'exists:doctors,id'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ], [
            'preferred_specialty_id.required_if' => 'Please select a specialty when choosing Option B (Specialty Selection).',
            'preferred_doctor_id.required_if' => 'Please select a doctor when choosing Option C (Doctor Selection).',
            'requested_time.date_format' => 'Please enter a valid time (e.g. 10:30).',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);

        // Run clinical symptom prediction
        $prediction = $this->predictionService->predict($validated['primary_complaint']);

        // Determine confirmed specialty and check for override
        $confirmedSpecialtyId = null;
        $isOverride = false;
        $overrideReason = $validated['override_reason'] ?? null;

        if ($validated['booking_mode'] === 'doctor' && !empty($validated['preferred_doctor_id'])) {
            $doc = Doctor::find($validated['preferred_doctor_id']);
            $confirmedSpecialtyId = $doc?->specialty_id;
            if ($confirmedSpecialtyId && $prediction['suggested_specialty_id'] && $confirmedSpecialtyId != $prediction['suggested_specialty_id']) {
                $isOverride = true;
            }
        } elseif ($validated['booking_mode'] === 'specialty' && !empty($validated['preferred_specialty_id'])) {
            $confirmedSpecialtyId = (int)$validated['preferred_specialty_id'];
            if ($prediction['suggested_specialty_id'] && $confirmedSpecialtyId != $prediction['suggested_specialty_id']) {
                $isOverride = true;
            }
        } else {
            // Auto booking defaults to predicted specialty
            $confirmedSpecialtyId = $prediction['suggested_specialty_id'];
        }

        $predictionData = [
            'predicted_specialty_id' => $prediction['suggested_specialty_id'],
            'predicted_specialty_name' => $prediction['suggested_specialty_name'],
            'confirmed_specialty_id' => $confirmedSpecialtyId,
            'is_override' => $isOverride,
            'override_reason' => $overrideReason,
            'confidence' => $prediction['confidence'],
            'explanation' => $prediction['explanation'],
            'is_urgent' => $prediction['is_urgent'],
            'urgent_instructions' => $prediction['urgent_instructions'],
            'matched_rule_id' => $prediction['matched_rule_id'],
        ];

        $allocation = $this->schedulingService->findAllocation([
            'patient_id' => $patient->id,
            'primary_complaint' => $validated['primary_complaint'],
            'booking_mode' => $validated['booking_mode'],
            'preferred_doctor_id' => ($validated['booking_mode'] === 'doctor') ? $validated['preferred_doctor_id'] : null,
            'preferred_specialty_id' => ($validated['booking_mode'] === 'specialty') ? $validated['preferred_specialty_id'] : null,
            'confirmed_specialty_id' => $confirmedSpecialtyId,
            'requested_date' => $validated['requested_date'],
            'requested_time' => $validated['requested_time'],
            'prediction_data' => $predictionData,
        ]);

        session(['pending_allocation' => [
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'patient_code' => $patient->patient_code,
            'doctor_id' => $allocation['doctor']->id,
            'doctor_name' => $allocation['doctor']->name,
            'doctor_specialty' => $allocation['doctor']->specialty->name,
            'consultation_room' => $allocation['doctor']->consultation_room,
            'requested_date' => $allocation['requested_date'],
            'requested_time' => $allocation['requested_time'],
            'assigned_date' => $allocation['assigned_date'],
            'assigned_time' => $allocation['assigned_time'],
            'end_time' => $allocation['end_time'],
            'primary_complaint' => $validated['primary_complaint'],
            'booking_mode' => $allocation['booking_mode'],
            'is_alternative_slot' => $allocation['is_alternative_slot'],
            'prediction_data' => $predictionData,
            'message' => $allocation['message'],
        ]]);

        return redirect()->route('appointments.confirm_preview');
    }

    /**
     * Show booking confirmation screen (Step 6).
     */
    public function confirmPreview()
    {
        $allocation = session('pending_allocation');

        if (!$allocation) {
            return redirect()->route('appointments.create')
                ->with('error', 'No pending appointment allocation found. Please start a new booking.');
        }

        return view('appointments.confirm_preview', compact('allocation'));
    }

    /**
     * Finalize and commit appointment with double-booking lock (Step 7).
     */
    public function confirmBooking(Request $request)
    {
        $allocation = session('pending_allocation');

        if (!$allocation) {
            return redirect()->route('appointments.create')
                ->with('error', 'Appointment allocation session expired. Please search again.');
        }

        try {
            $appointment = $this->schedulingService->bookAppointment($allocation, Auth::id());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('appointments.create', ['patient_id' => $allocation['patient_id'] ?? null])
                ->with('error', $e->getMessage() ?: 'The selected slot was taken or is no longer available. Please search again.');
        }

        // Clear session allocation
        session()->forget('pending_allocation');

        return redirect()->route('appointments.slip', $appointment)
            ->with('success', "Appointment #{$appointment->appointment_code} successfully booked!");
    }

    /**
     * Display appointment details.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor.specialty', 'creator', 'rescheduledFrom', 'prediction.predictedSpecialty', 'prediction.confirmedSpecialty']);
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Printable appointment slip (clean print CSS, suitable for A4 or receipt).
     */
    public function slip(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor.specialty']);
        $clinicName = ClinicSetting::get('clinic_name', 'CarePoint Medical Clinic');
        $clinicPhone = ClinicSetting::get('clinic_phone', '+1 (555) 234-5678');
        $clinicAddress = ClinicSetting::get('clinic_address', '742 Evergreen Healthcare Blvd');

        return view('appointments.slip', compact('appointment', 'clinicName', 'clinicPhone', 'clinicAddress'));
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Appointment::getStatuses())],
            'consultation_notes' => ['nullable', 'string'],
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'consultation_notes' => $validated['consultation_notes'] ?? $appointment->consultation_notes,
        ]);

        return redirect()->back()
            ->with('success', "Appointment status updated to '{$validated['status']}'.");
    }

    /**
     * Show reschedule form.
     */
    public function rescheduleForm(Appointment $appointment)
    {
        $doctors = Doctor::active()->with('specialty')->orderBy('name')->get();
        return view('appointments.reschedule', compact('appointment', 'doctors'));
    }

    /**
     * Execute reschedule.
     */
    public function reschedule(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'requested_time' => ['required', 'date_format:H:i,H:i:s'],
            'preferred_doctor_id' => ['nullable', 'exists:doctors,id'],
        ], [
            'requested_time.date_format' => 'Please enter a valid time (e.g. 10:30).',
        ]);

        $newAppointment = $this->schedulingService->reschedule(
            $appointment,
            $validated['requested_date'],
            $validated['requested_time'],
            $validated['preferred_doctor_id'] ?? null,
            Auth::id()
        );

        return redirect()->route('appointments.slip', $newAppointment)
            ->with('success', "Appointment successfully rescheduled to #{$newAppointment->appointment_code}.");
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->schedulingService->cancel($appointment, $validated['cancellation_reason'] ?? null);

        return redirect()->back()
            ->with('info', "Appointment #{$appointment->appointment_code} has been cancelled and the slot is released.");
    }
}
