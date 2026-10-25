<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientDeletionAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestrictedActionController extends Controller
{
    /**
     * Display the restricted patient deletion dashboard.
     */
    public function patientDeletionForm(Request $request)
    {
        $selectedPatient = null;
        $activeAppointmentsCount = 0;
        $pastAppointmentsCount = 0;

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->input('patient_id'));

            if ($selectedPatient) {
                $activeAppointmentsCount = Appointment::where('patient_id', $selectedPatient->id)
                    ->whereIn('status', [
                        Appointment::STATUS_SCHEDULED,
                        Appointment::STATUS_CHECKED_IN,
                        Appointment::STATUS_IN_CONSULTATION,
                    ])
                    ->count();

                $pastAppointmentsCount = Appointment::where('patient_id', $selectedPatient->id)
                    ->whereNotIn('status', [
                        Appointment::STATUS_SCHEDULED,
                        Appointment::STATUS_CHECKED_IN,
                        Appointment::STATUS_IN_CONSULTATION,
                    ])
                    ->count();
            }
        }

        $recentAudits = PatientDeletionAudit::orderBy('created_at', 'desc')->take(10)->get();

        return view('settings.restricted.patient_deletion', compact(
            'selectedPatient',
            'activeAppointmentsCount',
            'pastAppointmentsCount',
            'recentAudits'
        ));
    }

    /**
     * AJAX endpoint to search patients for restricted administrative operations.
     */
    public function searchPatient(Request $request)
    {
        $term = trim($request->query('q', ''));

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where('name', 'like', "%{$term}%")
            ->orWhere('patient_code', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit(15)
            ->get();

        $results = $patients->map(function ($patient) {
            $activeCount = Appointment::where('patient_id', $patient->id)
                ->whereIn('status', [
                    Appointment::STATUS_SCHEDULED,
                    Appointment::STATUS_CHECKED_IN,
                    Appointment::STATUS_IN_CONSULTATION,
                ])
                ->count();

            return [
                'id' => $patient->id,
                'patient_code' => $patient->patient_code,
                'name' => $patient->name,
                'phone' => $patient->phone,
                'age' => $patient->effective_age,
                'sex' => $patient->sex,
                'active_appointments_count' => $activeCount,
            ];
        });

        return response()->json($results);
    }

    /**
     * Execute permanent patient deletion with strict multi-step validation,
     * dependent appointment safeguards, transaction safety, and audit trail recording.
     */
    public function destroyPatient(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'confirm_patient_code' => ['required', 'string'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'acknowledge_irreversible' => ['accepted'],
        ], [
            'confirm_patient_code.required' => 'You must type the exact Patient ID to confirm deletion.',
            'reason.required' => 'A detailed operational or legal reason is mandatory for permanent patient deletion.',
            'reason.min' => 'The deletion reason must be at least 10 characters long.',
            'acknowledge_irreversible.accepted' => 'You must check the acknowledgement box confirming this action is permanent.',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $admin = Auth::user();

        // Step 1: Verification of typed confirmation code
        if (trim($validated['confirm_patient_code']) !== $patient->patient_code) {
            throw ValidationException::withMessages([
                'confirm_patient_code' => "The entered confirmation code '{$validated['confirm_patient_code']}' does not match the actual Patient ID '{$patient->patient_code}'.",
            ]);
        }

        // Step 2: Check for dependent active or upcoming appointments
        $activeAppointments = Appointment::where('patient_id', $patient->id)
            ->whereIn('status', [
                Appointment::STATUS_SCHEDULED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_IN_CONSULTATION,
            ])
            ->get();

        if ($activeAppointments->isNotEmpty()) {
            // Record failed audit attempt
            PatientDeletionAudit::create([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'patient_id_recorded' => $patient->id,
                'patient_code' => $patient->patient_code,
                'patient_name' => $patient->name,
                'patient_phone' => $patient->phone,
                'reason' => $validated['reason'],
                'ip_address' => $request->ip(),
                'outcome' => 'rejected_active_appointments',
                'notes' => "Blocked: Patient has {$activeAppointments->count()} active/upcoming appointments.",
                'archived_patient_summary' => [
                    'active_appointment_codes' => $activeAppointments->pluck('appointment_code')->toArray(),
                ],
            ]);

            throw ValidationException::withMessages([
                'active_appointments' => "Deletion Blocked: Patient {$patient->patient_code} has {$activeAppointments->count()} active or upcoming appointment(s). You must cancel or complete all active appointments before permanent deletion.",
            ]);
        }

        // Step 3: Transactional safe deletion with audit preservation
        $deletedCode = $patient->patient_code;
        $deletedName = $patient->name;

        DB::transaction(function () use ($patient, $admin, $validated, $request) {
            // Collect historical appointment records for archival in audit log
            $historicalAppointments = Appointment::where('patient_id', $patient->id)->get();

            $archivedSummary = [
                'patient' => [
                    'id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'email' => $patient->email,
                    'date_of_birth' => $patient->date_of_birth?->toDateString(),
                    'sex' => $patient->sex,
                    'blood_group' => $patient->blood_group,
                    'registered_at' => $patient->created_at?->toDateTimeString(),
                ],
                'historical_appointments_count' => $historicalAppointments->count(),
                'historical_appointments' => $historicalAppointments->map(fn($a) => [
                    'appointment_code' => $a->appointment_code,
                    'date' => $a->appointment_date?->toDateString(),
                    'time' => $a->appointment_time,
                    'status' => $a->status,
                    'doctor_id' => $a->doctor_id,
                ])->toArray(),
            ];

            // 1. Create immutable audit record
            PatientDeletionAudit::create([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'patient_id_recorded' => $patient->id,
                'patient_code' => $patient->patient_code,
                'patient_name' => $patient->name,
                'patient_phone' => $patient->phone,
                'reason' => $validated['reason'],
                'ip_address' => $request->ip(),
                'outcome' => 'success',
                'notes' => "Permanent deletion executed cleanly. {$historicalAppointments->count()} non-active appointment records purged.",
                'archived_patient_summary' => $archivedSummary,
            ]);

            // 2. Explicitly remove historical non-active appointments
            Appointment::where('patient_id', $patient->id)->delete();

            // 3. Delete patient record
            $patient->delete();
        });

        return redirect()->route('settings.restricted.patient_deletion')
            ->with('success', "Patient {$deletedName} ({$deletedCode}) has been permanently and safely deleted. Full audit trail recorded.");
    }
}
