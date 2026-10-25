<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentSchedulingService
{
    public function __construct(
        protected DoctorAvailabilityService $availabilityService
    ) {}

    /**
     * Find a suitable doctor and slot based on patient preferences.
     *
     * @param array $params [
     *   'patient_id' => int,
     *   'primary_complaint' => string,
     *   'preferred_doctor_id' => int|null,
     *   'preferred_specialty_id' => int|null,
     *   'requested_date' => string (Y-m-d),
     *   'requested_time' => string (H:i),
     *   'ignore_appointment_id' => int|null, // for rescheduling
     * ]
     * @return array [
     *   'success' => bool,
     *   'doctor' => Doctor,
     *   'patient' => Patient,
     *   'requested_date' => string,
     *   'requested_time' => string,
     *   'assigned_date' => string,
     *   'assigned_time' => string,
     *   'end_time' => string,
     *   'is_alternative_slot' => bool,
     *   'booking_mode' => string, // 'doctor', 'specialty', 'auto'
     *   'message' => string,
     * ]
     */
    public function findAllocation(array $params): array
    {
        $requestedDate = Carbon::parse($params['requested_date'])->startOfDay();
        $requestedTimeStr = Carbon::parse($params['requested_time'])->format('H:i:s');
        $requestedDateTime = Carbon::parse($requestedDate->toDateString() . ' ' . $requestedTimeStr);

        // Step 1: Validate date is not in past
        if ($requestedDateTime->isPast() && !$requestedDate->isToday()) {
            throw ValidationException::withMessages([
                'requested_date' => 'Requested appointment date cannot be in the past.',
            ]);
        }

        // If requested time is today and has already passed (or within 5 min), mark it so we allocate next available slot
        $isPastTimeToday = $requestedDate->isToday() && $requestedDateTime->lt(now()->subMinutes(5));

        $patient = Patient::findOrFail($params['patient_id']);

        // Step 2: Identify eligible doctors
        $eligibleDoctorsQuery = Doctor::active()->with('specialty');
        $bookingMode = $params['booking_mode'] ?? 'auto';

        if (!empty($params['preferred_doctor_id'])) {
            $eligibleDoctorsQuery->where('id', $params['preferred_doctor_id']);
            $bookingMode = 'doctor';
        } elseif (!empty($params['preferred_specialty_id'])) {
            $eligibleDoctorsQuery->where('specialty_id', $params['preferred_specialty_id']);
            $bookingMode = 'specialty';
        } elseif (!empty($params['confirmed_specialty_id'])) {
            $eligibleDoctorsQuery->where('specialty_id', $params['confirmed_specialty_id']);
            // Retain bookingMode as provided (defaults to 'auto' or 'specialty')
        }

        $eligibleDoctors = $eligibleDoctorsQuery->get();

        if ($eligibleDoctors->isEmpty()) {
            throw ValidationException::withMessages([
                'preferred_doctor_id' => 'No active doctors found matching the selected criteria.',
            ]);
        }

        $ignoreAppointmentId = $params['ignore_appointment_id'] ?? null;

        // Step 3 & 4: Check if the exact requested slot is available (only if not past time today)
        $availableAtRequestedTime = [];

        if (!$isPastTimeToday) {
            foreach ($eligibleDoctors as $doctor) {
                $durationMinutes = $doctor->consultation_duration > 0 ? $doctor->consultation_duration : 30;
                $slotStart = $requestedDateTime->copy();
                $slotEnd = $requestedDateTime->copy()->addMinutes($durationMinutes);

                if ($this->availabilityService->isDoctorAvailableForWindow($doctor, $slotStart, $slotEnd, $ignoreAppointmentId)) {
                    // Calculate doctor workload on this date
                    $workload = Appointment::where('doctor_id', $doctor->id)
                        ->whereDate('appointment_date', $requestedDate->toDateString())
                        ->where('status', '!=', Appointment::STATUS_CANCELLED)
                        ->when($ignoreAppointmentId, fn($q) => $q->where('id', '!=', $ignoreAppointmentId))
                        ->count();

                    $availableAtRequestedTime[] = [
                        'doctor' => $doctor,
                        'workload' => $workload,
                        'slot_start' => $slotStart,
                        'slot_end' => $slotEnd,
                    ];
                }
            }
        }

        // If found exact requested slot:
        if (!empty($availableAtRequestedTime)) {
            // Sort by workload asc, then doctor id asc (tie-breaker)
            usort($availableAtRequestedTime, function ($a, $b) {
                if ($a['workload'] === $b['workload']) {
                    return $a['doctor']->id <=> $b['doctor']->id;
                }
                return $a['workload'] <=> $b['workload'];
            });

            $best = $availableAtRequestedTime[0];

            return [
                'success' => true,
                'patient' => $patient,
                'doctor' => $best['doctor'],
                'requested_date' => $requestedDate->toDateString(),
                'requested_time' => $requestedTimeStr,
                'assigned_date' => $requestedDate->toDateString(),
                'assigned_time' => $best['slot_start']->format('H:i:s'),
                'end_time' => $best['slot_end']->format('H:i:s'),
                'is_alternative_slot' => false,
                'booking_mode' => $bookingMode,
                'prediction_data' => $params['prediction_data'] ?? null,
                'message' => 'Requested slot is available and allocated.',
            ];
        }

        // Step 5: Find the next available slot forward
        $horizonDays = (int) ClinicSetting::get('booking_horizon_days', 14);
        $searchStart = ($requestedDate->isToday() && $requestedDateTime->lt(now())) ? now() : $requestedDateTime->copy();

        for ($dayOffset = 0; $dayOffset <= $horizonDays; $dayOffset++) {
            $currentDate = $requestedDate->copy()->addDays($dayOffset)->startOfDay();
            $candidateSlots = [];

            foreach ($eligibleDoctors as $doctor) {
                $slots = $this->availabilityService->getDoctorSlotsForDate($doctor, $currentDate, $ignoreAppointmentId);

                // Filter slots that are available and on or after searchStart if on requested date
                $validSlots = $slots->filter(function ($slot) use ($searchStart, $currentDate) {
                    if (!$slot['is_available']) {
                        return false;
                    }
                    if ($currentDate->isSameDay($searchStart)) {
                        return $slot['start']->gte($searchStart);
                    }
                    return true;
                });

                if ($validSlots->isNotEmpty()) {
                    $earliestSlot = $validSlots->first();
                    $workload = Appointment::where('doctor_id', $doctor->id)
                        ->whereDate('appointment_date', $currentDate->toDateString())
                        ->where('status', '!=', Appointment::STATUS_CANCELLED)
                        ->when($ignoreAppointmentId, fn($q) => $q->where('id', '!=', $ignoreAppointmentId))
                        ->count();

                    $candidateSlots[] = [
                        'doctor' => $doctor,
                        'slot_start' => $earliestSlot['start'],
                        'slot_end' => $earliestSlot['end'],
                        'workload' => $workload,
                    ];
                }
            }

            if (!empty($candidateSlots)) {
                // Sort by slot_start asc, then workload asc, then doctor id asc
                usort($candidateSlots, function ($a, $b) {
                    $startDiff = $a['slot_start']->timestamp <=> $b['slot_start']->timestamp;
                    if ($startDiff !== 0) {
                        return $startDiff;
                    }
                    if ($a['workload'] !== $b['workload']) {
                        return $a['workload'] <=> $b['workload'];
                    }
                    return $a['doctor']->id <=> $b['doctor']->id;
                });

                $best = $candidateSlots[0];

                return [
                    'success' => true,
                    'patient' => $patient,
                    'doctor' => $best['doctor'],
                    'requested_date' => $requestedDate->toDateString(),
                    'requested_time' => $requestedTimeStr,
                    'assigned_date' => $best['slot_start']->toDateString(),
                    'assigned_time' => $best['slot_start']->format('H:i:s'),
                    'end_time' => $best['slot_end']->format('H:i:s'),
                    'is_alternative_slot' => true,
                    'booking_mode' => $bookingMode,
                    'prediction_data' => $params['prediction_data'] ?? null,
                    'message' => $isPastTimeToday
                        ? 'Requested time was in the past or unavailable. Earliest suitable upcoming slot allocated.'
                        : 'Requested slot was unavailable. Earliest suitable alternative allocated.',
                ];
            }
        }

        // If no slot was found within the booking horizon
        throw ValidationException::withMessages([
            'requested_date' => "No available appointment slots found within {$horizonDays} days for the selected criteria. Please choose a different date or specialty.",
        ]);
    }

    /**
     * Confirm and save appointment with concurrency protection.
     *
     * @param array $allocationData
     * @param int|null $createdByUserId
     * @return Appointment
     */
    public function bookAppointment(array $allocationData, ?int $createdByUserId = null): Appointment
    {
        return DB::transaction(function () use ($allocationData, $createdByUserId) {
            $doctorId = $allocationData['doctor_id'];
            $assignedDate = $allocationData['assigned_date'];
            $assignedTime = $allocationData['assigned_time'];
            $endTime = $allocationData['end_time'];
            $patientId = $allocationData['patient_id'];
            $primaryComplaint = $allocationData['primary_complaint'];
            $bookingMode = $allocationData['booking_mode'] ?? 'auto';
            $isAlternative = $allocationData['is_alternative_slot'] ?? false;
            $requestedDate = $allocationData['requested_date'] ?? $assignedDate;
            $requestedTime = $allocationData['requested_time'] ?? $assignedTime;
            $ignoreAppointmentId = $allocationData['rescheduled_from_id'] ?? null;

            // Concurrency Lock: Lock the doctor record for update
            $doctor = Doctor::where('id', $doctorId)->lockForUpdate()->firstOrFail();

            // Re-validate availability inside the transaction to prevent race condition double-booking
            $slotStart = Carbon::parse("{$assignedDate} {$assignedTime}");
            $slotEnd = Carbon::parse("{$assignedDate} {$endTime}");

            if (!$this->availabilityService->isDoctorAvailableForWindow($doctor, $slotStart, $slotEnd, $ignoreAppointmentId)) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'The selected slot has just been booked by another user. Please search again for an available slot.',
                ]);
            }

            // Generate unique appointment code: APT-YYYYMMDD-XXXX
            $datePrefix = Carbon::parse($assignedDate)->format('Ymd');
            $countToday = Appointment::whereDate('created_at', now()->toDateString())->count() + 1;
            $appointmentCode = 'APT-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            // Ensure unique appointment code
            while (Appointment::where('appointment_code', $appointmentCode)->exists()) {
                $countToday++;
                $appointmentCode = 'APT-' . $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
            }

            // Create appointment record
            $appointment = Appointment::create([
                'appointment_code' => $appointmentCode,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'requested_date' => $requestedDate,
                'requested_time' => $requestedTime,
                'appointment_date' => $assignedDate,
                'appointment_time' => $assignedTime,
                'end_time' => $endTime,
                'primary_complaint' => $primaryComplaint,
                'status' => Appointment::STATUS_SCHEDULED,
                'booking_mode' => $bookingMode,
                'is_alternative_slot' => $isAlternative,
                'rescheduled_from_id' => $ignoreAppointmentId,
                'created_by' => $createdByUserId,
            ]);

            // Save clinical symptom prediction history if present
            if (!empty($allocationData['prediction_data']) && is_array($allocationData['prediction_data'])) {
                $pred = $allocationData['prediction_data'];
                \App\Models\AppointmentPrediction::create([
                    'appointment_id' => $appointment->id,
                    'primary_complaint' => $primaryComplaint,
                    'predicted_specialty_id' => $pred['predicted_specialty_id'] ?? null,
                    'confirmed_specialty_id' => $pred['confirmed_specialty_id'] ?? $doctor->specialty_id,
                    'is_override' => (bool)($pred['is_override'] ?? false),
                    'override_reason' => $pred['override_reason'] ?? null,
                    'confidence' => $pred['confidence'] ?? 'Medium',
                    'explanation' => $pred['explanation'] ?? null,
                    'is_urgent' => (bool)($pred['is_urgent'] ?? false),
                    'matched_rule_id' => $pred['matched_rule_id'] ?? null,
                ]);
            }

            // If this is a reschedule, update the original appointment status
            if ($ignoreAppointmentId) {
                $oldAppointment = Appointment::find($ignoreAppointmentId);
                if ($oldAppointment) {
                    $oldAppointment->update([
                        'status' => Appointment::STATUS_CANCELLED,
                        'cancellation_reason' => "Rescheduled to appointment #{$appointment->appointment_code}",
                    ]);
                }
            }

            return $appointment;
        });
    }

    /**
     * Reschedule an existing appointment.
     */
    public function reschedule(Appointment $appointment, string $newDate, string $newTime, ?int $preferredDoctorId = null, ?int $userId = null): Appointment
    {
        $allocation = $this->findAllocation([
            'patient_id' => $appointment->patient_id,
            'primary_complaint' => $appointment->primary_complaint,
            'preferred_doctor_id' => $preferredDoctorId ?? $appointment->doctor_id,
            'preferred_specialty_id' => null,
            'requested_date' => $newDate,
            'requested_time' => $newTime,
            'ignore_appointment_id' => $appointment->id,
        ]);

        return $this->bookAppointment([
            'doctor_id' => $allocation['doctor']->id,
            'patient_id' => $allocation['patient']->id,
            'assigned_date' => $allocation['assigned_date'],
            'assigned_time' => $allocation['assigned_time'],
            'end_time' => $allocation['end_time'],
            'requested_date' => $allocation['requested_date'],
            'requested_time' => $allocation['requested_time'],
            'primary_complaint' => $appointment->primary_complaint,
            'booking_mode' => $allocation['booking_mode'],
            'is_alternative_slot' => $allocation['is_alternative_slot'],
            'rescheduled_from_id' => $appointment->id,
        ], $userId);
    }

    /**
     * Cancel an appointment and free the slot.
     */
    public function cancel(Appointment $appointment, ?string $reason = null): bool
    {
        return $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => $reason ?? 'Cancelled by reception staff',
        ]);
    }
}
