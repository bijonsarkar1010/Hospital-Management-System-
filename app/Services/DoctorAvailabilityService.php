<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DoctorAvailabilityService
{
    /**
     * Check if a doctor is on leave or unavailable on a specific date and time.
     */
    public function isDoctorOnLeave(Doctor $doctor, Carbon $date, ?Carbon $time = null): bool
    {
        $dateStr = $date->toDateString();

        $query = DoctorUnavailability::where('doctor_id', $doctor->id)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr);

        $unavailabilities = $query->get();

        if ($unavailabilities->isEmpty()) {
            return false;
        }

        foreach ($unavailabilities as $unavail) {
            // If full day leave
            if (empty($unavail->start_time) || empty($unavail->end_time)) {
                return true;
            }

            // If time is provided, check if time overlaps
            if ($time) {
                $timeStr = $time->format('H:i:s');
                if ($timeStr >= $unavail->start_time && $timeStr < $unavail->end_time) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specific time falls within a doctor's break.
     */
    public function isDuringBreak(Doctor $doctor, Carbon $slotStart, Carbon $slotEnd): bool
    {
        $dayOfWeek = $slotStart->dayOfWeek;
        $slotStartTime = $slotStart->format('H:i:s');
        $slotEndTime = $slotEnd->format('H:i:s');

        $breaks = DoctorBreak::where('doctor_id', $doctor->id)
            ->where(function ($q) use ($dayOfWeek) {
                $q->whereNull('day_of_week')
                  ->orWhere('day_of_week', $dayOfWeek);
            })
            ->get();

        foreach ($breaks as $break) {
            // Overlap condition: slotStart < breakEnd && slotEnd > breakStart
            if ($slotStartTime < $break->end_time && $slotEndTime > $break->start_time) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the recurring schedule for a doctor on a specific day of the week.
     */
    public function getScheduleForDay(Doctor $doctor, int $dayOfWeek): ?DoctorSchedule
    {
        return DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();
    }

    /**
     * Generate dynamic time slots for a doctor on a specific date.
     * Returns a collection of slot arrays:
     * ['start' => Carbon, 'end' => Carbon, 'is_available' => bool, 'reason' => string|null]
     */
    public function getDoctorSlotsForDate(Doctor $doctor, Carbon $date, ?int $ignoreAppointmentId = null): Collection
    {
        $slots = collect();

        // 1. Check if doctor is active
        if (!$doctor->is_active) {
            return $slots;
        }

        // 2. Check full day leave
        if ($this->isDoctorOnLeave($doctor, $date)) {
            // Check if full day or partial
            $isFullDay = DoctorUnavailability::where('doctor_id', $doctor->id)
                ->whereDate('start_date', '<=', $date->toDateString())
                ->whereDate('end_date', '>=', $date->toDateString())
                ->whereNull('start_time')
                ->exists();

            if ($isFullDay) {
                return $slots; // No slots on full day leave
            }
        }

        // 3. Get doctor's working schedule for this day
        $schedule = $this->getScheduleForDay($doctor, $date->dayOfWeek);
        if (!$schedule) {
            return $slots; // Doctor does not work on this day
        }

        $durationMinutes = $doctor->consultation_duration > 0 ? $doctor->consultation_duration : 30;

        // Clinic operating hours bounds
        $clinicOpen = ClinicSetting::get('clinic_open_time', '08:00');
        $clinicClose = ClinicSetting::get('clinic_close_time', '20:00');

        $schedStart = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
        $schedEnd = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);

        $clinicStart = Carbon::parse($date->toDateString() . ' ' . $clinicOpen);
        $clinicEnd = Carbon::parse($date->toDateString() . ' ' . $clinicClose);

        // Effective bounds
        $startTime = $schedStart->max($clinicStart);
        $endTime = $schedEnd->min($clinicEnd);

        if ($startTime->gte($endTime)) {
            return $slots;
        }

        // 4. Fetch existing non-cancelled appointments for this doctor on this date
        $existingAppointmentsQuery = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', Appointment::STATUS_CANCELLED);

        if ($ignoreAppointmentId) {
            $existingAppointmentsQuery->where('id', '!=', $ignoreAppointmentId);
        }

        $existingAppointments = $existingAppointmentsQuery->get();

        // Generate slots
        $currentSlot = $startTime->copy();

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endTime)) {
            $slotStart = $currentSlot->copy();
            $slotEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            $isAvailable = true;
            $unavailabilityReason = null;

            // Check if slot is in the past
            if ($date->isToday() && $slotStart->isPast()) {
                $isAvailable = false;
                $unavailabilityReason = 'Past time';
            }

            // Check break
            if ($isAvailable && $this->isDuringBreak($doctor, $slotStart, $slotEnd)) {
                $isAvailable = false;
                $unavailabilityReason = 'Doctor break';
            }

            // Check partial leave/unavailability
            if ($isAvailable && $this->isDoctorOnLeave($doctor, $date, $slotStart)) {
                $isAvailable = false;
                $unavailabilityReason = 'Doctor unavailable/on leave';
            }

            // Check existing appointment overlap
            if ($isAvailable) {
                $slotStartStr = $slotStart->format('H:i:s');
                $slotEndStr = $slotEnd->format('H:i:s');

                foreach ($existingAppointments as $appointment) {
                    if ($slotStartStr < $appointment->end_time && $slotEndStr > $appointment->appointment_time) {
                        $isAvailable = false;
                        $unavailabilityReason = 'Booked (' . $appointment->status . ')';
                        break;
                    }
                }
            }

            $slots->push([
                'start' => $slotStart,
                'end' => $slotEnd,
                'start_formatted' => $slotStart->format('h:i A'),
                'end_formatted' => $slotEnd->format('h:i A'),
                'is_available' => $isAvailable,
                'reason' => $unavailabilityReason,
            ]);

            $currentSlot->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /**
     * Check if a doctor is available for an exact slot window.
     */
    public function isDoctorAvailableForWindow(Doctor $doctor, Carbon $slotStart, Carbon $slotEnd, ?int $ignoreAppointmentId = null): bool
    {
        $date = $slotStart->copy()->startOfDay();

        // Check if doctor is active
        if (!$doctor->is_active) {
            return false;
        }

        // Check leave
        if ($this->isDoctorOnLeave($doctor, $date, $slotStart)) {
            return false;
        }

        // Check schedule
        $schedule = $this->getScheduleForDay($doctor, $date->dayOfWeek);
        if (!$schedule) {
            return false;
        }

        $slotStartStr = $slotStart->format('H:i:s');
        $slotEndStr = $slotEnd->format('H:i:s');

        // Check within doctor working hours
        if ($slotStartStr < $schedule->start_time || $slotEndStr > $schedule->end_time) {
            return false;
        }

        // Check clinic hours
        $clinicOpen = ClinicSetting::get('clinic_open_time', '08:00');
        $clinicClose = ClinicSetting::get('clinic_close_time', '20:00');
        if ($slotStartStr < $clinicOpen || $slotEndStr > $clinicClose) {
            return false;
        }

        // Check break
        if ($this->isDuringBreak($doctor, $slotStart, $slotEnd)) {
            return false;
        }

        // Check appointment overlaps
        $conflictQuery = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->where('appointment_time', '<', $slotEndStr)
            ->where('end_time', '>', $slotStartStr);

        if ($ignoreAppointmentId) {
            $conflictQuery->where('id', '!=', $ignoreAppointmentId);
        }

        return !$conflictQuery->exists();
    }

    /**
     * Get real-time status and information for a doctor.
     * Evaluates:
     * - On duty and available
     * - On duty but currently consulting
     * - On duty with a fully booked schedule
     * - On break or temporarily unavailable
     * - Off duty
     */
    public function getDoctorCurrentStatus(Doctor $doctor, ?Carbon $now = null): array
    {
        $now = $now ? $now->copy() : now();
        $date = $now->copy()->startOfDay();
        $currentTimeStr = $now->format('H:i:s');
        $dayOfWeek = $now->dayOfWeek;

        $appointmentsToday = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->orderBy('appointment_time')
            ->get();

        $schedule = $this->getScheduleForDay($doctor, $dayOfWeek);

        // 1. Check if doctor is active
        if (!$doctor->is_active) {
            return [
                'status_code' => 'inactive',
                'status_label' => 'Inactive',
                'badge_class' => 'badge bg-secondary',
                'current_appointment' => null,
                'next_available_slot' => null,
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        // 2. Check if on leave today
        if ($this->isDoctorOnLeave($doctor, $date)) {
            return [
                'status_code' => 'on_leave',
                'status_label' => 'On Leave / Holiday',
                'badge_class' => 'badge bg-secondary',
                'current_appointment' => null,
                'next_available_slot' => null,
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        // 3. Check if scheduled to work today
        if (!$schedule) {
            return [
                'status_code' => 'off_duty',
                'status_label' => 'Off Duty',
                'badge_class' => 'badge bg-secondary text-white',
                'current_appointment' => null,
                'next_available_slot' => null,
                'appointments_today_count' => 0,
            ];
        }

        // 4. Check if current time is within working hours
        $isWithinWorkingHours = ($currentTimeStr >= $schedule->start_time && $currentTimeStr <= $schedule->end_time);

        if (!$isWithinWorkingHours) {
            return [
                'status_code' => 'off_duty',
                'status_label' => 'Off Duty',
                'badge_class' => 'badge bg-secondary text-white',
                'current_appointment' => null,
                'next_available_slot' => null,
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        // 5. Check if currently on break
        if ($this->isDuringBreak($doctor, $now, $now->copy()->addMinutes(5))) {
            return [
                'status_code' => 'on_break',
                'status_label' => 'On Break',
                'badge_class' => 'badge bg-warning text-dark',
                'current_appointment' => null,
                'next_available_slot' => $this->getNextAvailableSlotForDoctor($doctor, $now),
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        // 6. Check if currently consulting (has appointment right now)
        $currentAppointment = $appointmentsToday->first(function ($apt) use ($currentTimeStr) {
            return ($apt->appointment_time <= $currentTimeStr && $apt->end_time >= $currentTimeStr);
        });

        // 7. Check all slots today to see if fully booked
        $todaySlots = $this->getDoctorSlotsForDate($doctor, $date);
        $availableSlotsCount = $todaySlots->where('is_available', true)->count();

        $nextSlot = $this->getNextAvailableSlotForDoctor($doctor, $now);

        if ($currentAppointment) {
            return [
                'status_code' => 'consulting',
                'status_label' => 'In Consultation',
                'badge_class' => 'badge bg-warning text-dark',
                'current_appointment' => $currentAppointment,
                'next_available_slot' => $nextSlot,
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        if ($todaySlots->isNotEmpty() && $availableSlotsCount === 0) {
            return [
                'status_code' => 'fully_booked',
                'status_label' => 'Fully Booked Today',
                'badge_class' => 'badge bg-danger text-white',
                'current_appointment' => null,
                'next_available_slot' => $nextSlot,
                'appointments_today_count' => $appointmentsToday->count(),
            ];
        }

        return [
            'status_code' => 'available',
            'status_label' => 'On Duty & Available',
            'badge_class' => 'badge bg-success text-white',
            'current_appointment' => null,
            'next_available_slot' => $nextSlot,
            'appointments_today_count' => $appointmentsToday->count(),
        ];
    }

    /**
     * Find the next available slot for a doctor starting from a given date/time.
     */
    public function getNextAvailableSlotForDoctor(Doctor $doctor, ?Carbon $fromDateTime = null, int $horizonDays = 14): ?array
    {
        $start = $fromDateTime ? $fromDateTime->copy() : now();
        $bookingHorizon = (int) ClinicSetting::get('booking_horizon_days', $horizonDays);

        for ($d = 0; $d <= $bookingHorizon; $d++) {
            $checkDate = $start->copy()->addDays($d)->startOfDay();

            $slots = $this->getDoctorSlotsForDate($doctor, $checkDate);

            // Filter slots after start time if today
            $validSlots = $slots->filter(function ($slot) use ($start, $checkDate) {
                if (!$slot['is_available']) {
                    return false;
                }
                if ($checkDate->isSameDay($start)) {
                    return $slot['start']->gte($start);
                }
                return true;
            });

            if ($validSlots->isNotEmpty()) {
                $earliest = $validSlots->first();
                return [
                    'date' => $checkDate->toDateString(),
                    'date_formatted' => $checkDate->format('M d, Y'),
                    'start_time' => $earliest['start']->format('H:i:s'),
                    'end_time' => $earliest['end']->format('H:i:s'),
                    'start_formatted' => $earliest['start_formatted'],
                    'end_formatted' => $earliest['end_formatted'],
                    'doctor' => $doctor,
                ];
            }
        }

        return null;
    }
}
