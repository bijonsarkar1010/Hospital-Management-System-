<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailability;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    public function __construct(
        protected DoctorAvailabilityService $availabilityService
    ) {}

    /**
     * Weekly Duty Roster overview showing all doctors and their active working days.
     */
    public function index(Request $request)
    {
        $doctors = Doctor::active()->with(['specialty', 'schedules', 'unavailabilities' => function ($q) {
            $q->where('end_date', '>=', now()->toDateString());
        }])->orderBy('name')->get();

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            0 => 'Sunday',
        ];

        return view('schedules.index', compact('doctors', 'days'));
    }

    /**
     * Manage weekly working hours, breaks, and leaves for a specific doctor.
     */
    public function manage(Doctor $doctor)
    {
        $doctor->load(['specialty', 'schedules', 'breaks', 'unavailabilities']);

        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            0 => 'Sunday',
        ];

        return view('schedules.manage', compact('doctor', 'days'));
    }

    /**
     * Update recurring weekly schedule with conflict detection.
     */
    public function updateSchedule(Request $request, Doctor $doctor)
    {
        $schedulesInput = $request->input('schedules', []);

        // Check if modifying this schedule conflicts with future booked appointments
        $futureAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', '>=', now()->toDateString())
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->get();

        $conflicts = [];

        foreach ($schedulesInput as $day => $data) {
            $dayInt = (int)$day;
            $isActive = isset($data['active']) && $data['active'] == '1';

            if (!$isActive) {
                // If day disabled, check if future appointments exist on that day of week
                $dayAppointments = $futureAppointments->filter(function ($apt) use ($dayInt) {
                    return Carbon::parse($apt->appointment_date)->dayOfWeek === $dayInt;
                });

                if ($dayAppointments->isNotEmpty()) {
                    $conflicts[] = "Cannot deactivate " . DoctorSchedule::getDayName($dayInt) . " because {$dayAppointments->count()} booked appointment(s) exist on that weekday.";
                }
            } else {
                // If start/end time modified, check if existing appointments fall outside new hours
                $newStart = $data['start_time'];
                $newEnd = $data['end_time'];

                $outsideAppointments = $futureAppointments->filter(function ($apt) use ($dayInt, $newStart, $newEnd) {
                    if (Carbon::parse($apt->appointment_date)->dayOfWeek !== $dayInt) {
                        return false;
                    }
                    return ($apt->appointment_time < $newStart || $apt->end_time > $newEnd);
                });

                if ($outsideAppointments->isNotEmpty()) {
                    $conflicts[] = "New hours on " . DoctorSchedule::getDayName($dayInt) . " ({$newStart} - {$newEnd}) exclude {$outsideAppointments->count()} existing appointment(s).";
                }
            }
        }

        if (!empty($conflicts)) {
            return redirect()->back()
                ->withInput()
                ->with('error_conflicts', $conflicts);
        }

        // Save schedule updates
        foreach ($schedulesInput as $day => $data) {
            $dayInt = (int)$day;
            $isActive = isset($data['active']) && $data['active'] == '1';

            if ($isActive && !empty($data['start_time']) && !empty($data['end_time'])) {
                DoctorSchedule::updateOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $dayInt],
                    [
                        'start_time' => $data['start_time'],
                        'end_time' => $data['end_time'],
                        'is_available' => true,
                    ]
                );
            } else {
                DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $dayInt)
                    ->delete();
            }
        }

        return redirect()->route('schedules.manage', $doctor)
            ->with('success', 'Weekly working hours updated successfully.');
    }

    /**
     * Add a break period.
     */
    public function addBreak(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
        ]);

        $validated['doctor_id'] = $doctor->id;
        DoctorBreak::create($validated);

        return redirect()->back()->with('success', 'Break period added successfully.');
    }

    /**
     * Delete a break period.
     */
    public function deleteBreak(DoctorBreak $break)
    {
        $doctorId = $break->doctor_id;
        $break->delete();

        return redirect()->back()->with('success', 'Break removed successfully.');
    }

    /**
     * Add doctor leave / unavailability.
     */
    public function addLeave(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'type' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:250'],
        ]);

        $validated['doctor_id'] = $doctor->id;
        DoctorUnavailability::create($validated);

        return redirect()->back()->with('success', 'Leave / unavailability recorded successfully.');
    }

    /**
     * Delete doctor leave record.
     */
    public function deleteLeave(DoctorUnavailability $unavailability)
    {
        $unavailability->delete();
        return redirect()->back()->with('success', 'Leave record removed.');
    }

    /**
     * Get dynamic available slots for a doctor on a specific date (JSON API for UI).
     */
    public function getSlots(Request $request, Doctor $doctor)
    {
        $dateStr = $request->query('date', now()->toDateString());
        $date = Carbon::parse($dateStr);

        $slots = $this->availabilityService->getDoctorSlotsForDate($doctor, $date);

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'room' => $doctor->consultation_room,
                'duration' => $doctor->consultation_duration,
            ],
            'date' => $date->toDateString(),
            'slots' => $slots->values(),
        ]);
    }
}
