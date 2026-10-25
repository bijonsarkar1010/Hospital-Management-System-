<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Services\AppointmentSchedulingService;
use App\Services\SymptomSpecialtyPredictionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function __construct(
        protected AppointmentSchedulingService $schedulingService,
        protected SymptomSpecialtyPredictionService $predictionService
    ) {}

    /**
     * Display searchable patient directory.
     */
    public function index(Request $request)
    {
        $query = Patient::with(['lastAppointment.doctor', 'upcomingAppointment.doctor']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->input('blood_group'));
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    /**
     * Show registration form (with integrated appointment request).
     */
    public function create()
    {
        $specialties = Specialty::orderBy('name')->get();
        $doctors = Doctor::active()->with('specialty')->orderBy('name')->get();

        return view('patients.create', compact('specialties', 'doctors'));
    }

    /**
     * Check if a patient with the given phone number already exists (AJAX endpoint).
     */
    public function checkDuplicate(Request $request)
    {
        $phone = trim($request->query('phone', ''));
        if (strlen($phone) < 7) {
            return response()->json(['exists' => false]);
        }

        // Clean digits
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $patient = Patient::where('phone', 'like', "%{$cleanPhone}%")
            ->orWhere('phone', $phone)
            ->first();

        if ($patient) {
            return response()->json([
                'exists' => true,
                'patient' => [
                    'id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'age' => $patient->effective_age,
                    'sex' => $patient->sex,
                    'url' => route('patients.show', $patient),
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Store new patient and immediately transition to appointment scheduling allocation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Patient details
            'name' => ['required', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'sex' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'blood_group' => ['nullable', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])],
            'diabetes_status' => ['nullable', 'string', Rule::in(['Diabetic', 'Non-Diabetic', 'Unknown'])],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:50', 'max:260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:30', 'max:180'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'existing_conditions' => ['nullable', 'string', 'max:500'],
            'current_medications' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'clinical_notes' => ['nullable', 'string'],

            // Appointment request details
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

        // Auto-calculate age if DOB provided
        if (!empty($validated['date_of_birth']) && empty($validated['age'])) {
            $validated['age'] = Carbon::parse($validated['date_of_birth'])->age;
        }

        // Generate unique patient code: PAT-YYYY-XXXX
        $year = now()->format('Y');
        $count = Patient::whereYear('created_at', $year)->count() + 1;
        $patientCode = 'PAT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        while (Patient::where('patient_code', $patientCode)->exists()) {
            $count++;
            $patientCode = 'PAT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $validated['patient_code'] = $patientCode;

        $patient = Patient::create($validated);

        // Run clinical symptom prediction
        $prediction = $this->predictionService->predict($validated['primary_complaint']);

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

        // Run scheduling engine to find proposed allocation with enforced specialty constraint
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

        // Store allocation in session for receptionist review & confirmation
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

        return redirect()->route('appointments.confirm_preview')
            ->with('success', "Patient record #{$patientCode} created successfully.");
    }

    /**
     * Show single patient profile with appointment history.
     */
    public function show(Patient $patient)
    {
        $patient->load(['appointments.doctor.specialty']);
        return view('patients.show', compact('patient'));
    }

    /**
     * Show edit patient form.
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update patient record.
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'sex' => ['required', 'string', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'blood_group' => ['nullable', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])],
            'diabetes_status' => ['nullable', 'string', Rule::in(['Diabetic', 'Non-Diabetic', 'Unknown'])],
            'blood_pressure_systolic' => ['nullable', 'integer', 'min:50', 'max:260'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'min:30', 'max:180'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'existing_conditions' => ['nullable', 'string', 'max:500'],
            'current_medications' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'clinical_notes' => ['nullable', 'string'],
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient information updated successfully.');
    }
}
