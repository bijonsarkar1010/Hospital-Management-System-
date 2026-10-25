<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DoctorAvailabilityService $availabilityService
    ) {}

    public function index(Request $request)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Summary Cards
        $totalPatients = Patient::count();
        $todayAppointmentsCount = Appointment::whereDate('appointment_date', $today->toDateString())->count();
        $upcomingCount = Appointment::where('appointment_date', '>=', $today->toDateString())
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN])
            ->count();
        $completedTodayCount = Appointment::whereDate('appointment_date', $today->toDateString())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();
        $cancelledTodayCount = Appointment::whereDate('appointment_date', $today->toDateString())
            ->where('status', Appointment::STATUS_CANCELLED)
            ->count();

        // 2. Doctor Availability Panel
        $activeDoctors = Doctor::active()->with('specialty')->get();
        $doctorStatuses = [];
        $onDutyCount = 0;
        $availableTodayCount = 0;

        foreach ($activeDoctors as $doc) {
            $status = $this->availabilityService->getDoctorCurrentStatus($doc, $now);
            $doctorStatuses[] = [
                'doctor' => $doc,
                'status' => $status,
            ];

            if (in_array($status['status_code'], ['available', 'consulting', 'fully_booked', 'on_break'])) {
                $onDutyCount++;
            }
            if ($status['status_code'] === 'available') {
                $availableTodayCount++;
            }
        }

        // 3. Today's Appointment Table with search, filter, and pagination
        $todayQuery = Appointment::with(['patient', 'doctor.specialty'])
            ->whereDate('appointment_date', $today->toDateString());

        if ($request->filled('today_search')) {
            $search = trim($request->input('today_search'));
            $todayQuery->where(function ($q) use ($search) {
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

        if ($request->filled('today_status')) {
            $todayQuery->where('status', $request->input('today_status'));
        }

        if ($request->filled('today_specialty')) {
            $todayQuery->whereHas('doctor', function ($q) use ($request) {
                $q->where('specialty_id', $request->input('today_specialty'));
            });
        }

        $todayAppointments = $todayQuery->orderBy('appointment_time', 'asc')
            ->paginate(10, ['*'], 'today_page')
            ->withQueryString();

        // 4. Upcoming Appointments with quick filters
        $upcomingFilter = $request->input('upcoming_filter', 'next_7_days');
        $upcomingQuery = Appointment::with(['patient', 'doctor.specialty'])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN]);

        if ($upcomingFilter === 'today') {
            $upcomingQuery->whereDate('appointment_date', $today->toDateString());
        } elseif ($upcomingFilter === 'tomorrow') {
            $upcomingQuery->whereDate('appointment_date', $today->copy()->addDay()->toDateString());
        } elseif ($upcomingFilter === 'next_7_days') {
            $upcomingQuery->whereBetween('appointment_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()]);
        } elseif ($upcomingFilter === 'custom' && $request->filled('custom_start') && $request->filled('custom_end')) {
            $upcomingQuery->whereBetween('appointment_date', [$request->input('custom_start'), $request->input('custom_end')]);
        } else {
            $upcomingQuery->whereBetween('appointment_date', [$today->toDateString(), $today->copy()->addDays(7)->toDateString()]);
        }

        $upcomingAppointments = $upcomingQuery->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(8)
            ->get();

        $specialties = Specialty::orderBy('name')->get();
        $statuses = Appointment::getStatuses();

        return view('dashboard', compact(
            'totalPatients',
            'todayAppointmentsCount',
            'upcomingCount',
            'onDutyCount',
            'availableTodayCount',
            'completedTodayCount',
            'cancelledTodayCount',
            'todayAppointments',
            'upcomingAppointments',
            'doctorStatuses',
            'specialties',
            'statuses',
            'upcomingFilter'
        ));
    }
}
