<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display reports dashboard with filters and summaries.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $doctorId = $request->input('doctor_id');
        $specialtyId = $request->input('specialty_id');
        $status = $request->input('status');

        $baseQuery = Appointment::whereBetween('appointment_date', [$startDate, $endDate]);

        if ($doctorId) {
            $baseQuery->where('doctor_id', $doctorId);
        }
        if ($specialtyId) {
            $baseQuery->whereHas('doctor', function ($q) use ($specialtyId) {
                $q->where('specialty_id', $specialtyId);
            });
        }
        if ($status) {
            $baseQuery->where('status', $status);
        }

        // Summary KPI Counts
        $totalAppointments = (clone $baseQuery)->count();
        $completedCount = (clone $baseQuery)->where('status', Appointment::STATUS_COMPLETED)->count();
        $cancelledCount = (clone $baseQuery)->where('status', Appointment::STATUS_CANCELLED)->count();
        $noShowCount = (clone $baseQuery)->where('status', Appointment::STATUS_NO_SHOW)->count();
        $scheduledCount = (clone $baseQuery)->where('status', Appointment::STATUS_SCHEDULED)->count();

        $newPatientsCount = Patient::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ])->count();

        // 1. Appointments grouped by Doctor
        $doctorStats = (clone $baseQuery)
            ->selectRaw('doctor_id, COUNT(*) as total, 
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = "No Show" THEN 1 ELSE 0 END) as no_show')
            ->groupBy('doctor_id')
            ->with('doctor.specialty')
            ->get();

        // 2. Appointments grouped by Specialty
        $specialtyStats = (clone $baseQuery)
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
            ->selectRaw('specialties.id, specialties.name as specialty_name, COUNT(appointments.id) as total')
            ->groupBy('specialties.id', 'specialties.name')
            ->orderByDesc('total')
            ->get();

        // 3. Appointments grouped by Date
        $dateStats = (clone $baseQuery)
            ->selectRaw('appointment_date, COUNT(*) as total,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled')
            ->groupBy('appointment_date')
            ->orderBy('appointment_date', 'desc')
            ->take(15)
            ->get();

        $doctors = Doctor::active()->orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        $statuses = Appointment::getStatuses();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'doctorId',
            'specialtyId',
            'status',
            'totalAppointments',
            'completedCount',
            'cancelledCount',
            'noShowCount',
            'scheduledCount',
            'newPatientsCount',
            'doctorStats',
            'specialtyStats',
            'dateStats',
            'doctors',
            'specialties',
            'statuses'
        ));
    }

    /**
     * Export appointments to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $doctorId = $request->input('doctor_id');
        $specialtyId = $request->input('specialty_id');
        $status = $request->input('status');

        $query = Appointment::with(['patient', 'doctor.specialty'])
            ->whereBetween('appointment_date', [$startDate, $endDate]);

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }
        if ($specialtyId) {
            $query->whereHas('doctor', function ($q) use ($specialtyId) {
                $q->where('specialty_id', $specialtyId);
            });
        }
        if ($status) {
            $query->where('status', $status);
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();

        $filename = "appointments_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($appointments) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Appointment Code',
                'Patient Code',
                'Patient Name',
                'Patient Age',
                'Patient Sex',
                'Patient Phone',
                'Doctor Code',
                'Doctor Name',
                'Specialty',
                'Consultation Room',
                'Appointment Date',
                'Start Time',
                'End Time',
                'Status',
                'Booking Mode',
                'Is Alternative Slot',
                'Primary Complaint',
            ]);

            foreach ($appointments as $apt) {
                fputcsv($handle, [
                    $apt->appointment_code,
                    $apt->patient->patient_code ?? '',
                    $apt->patient->name ?? '',
                    $apt->patient->effective_age ?? '',
                    $apt->patient->sex ?? '',
                    $apt->patient->phone ?? '',
                    $apt->doctor->doctor_code ?? '',
                    $apt->doctor->name ?? '',
                    $apt->doctor->specialty->name ?? '',
                    $apt->doctor->consultation_room ?? '',
                    $apt->appointment_date->format('Y-m-d'),
                    $apt->appointment_time,
                    $apt->end_time,
                    $apt->status,
                    $apt->booking_mode,
                    $apt->is_alternative_slot ? 'Yes' : 'No',
                    $apt->primary_complaint,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
    public function dailyReport(\Illuminate\Http\Request $request)
    {
        $reportDate = $request->input('date', now()->toDateString());
        $timeFrame = $request->input('time_frame', '24h');
        $startTime = $request->input('start_time', '00:00');
        $endTime = $request->input('end_time', '23:59');

        $query = \App\Models\Appointment::with(['patient', 'doctor.specialty'])
            ->whereDate('appointment_date', $reportDate);

        if ($timeFrame === 'custom') {
            $query->where('appointment_time', '>=', $startTime . ':00')
                  ->where('appointment_time', '<=', $endTime . ':00');
        }

        $appointments = $query->orderBy('appointment_time', 'asc')->get();

        $totalAppointments = $appointments->count();
        $completedCount = $appointments->where('status', \App\Models\Appointment::STATUS_COMPLETED)->count();
        $inConsultationCount = $appointments->where('status', \App\Models\Appointment::STATUS_IN_CONSULTATION)->count();
        $checkedInCount = $appointments->where('status', \App\Models\Appointment::STATUS_CHECKED_IN)->count();
        $scheduledCount = $appointments->where('status', \App\Models\Appointment::STATUS_SCHEDULED)->count();
        $cancelledCount = $appointments->where('status', \App\Models\Appointment::STATUS_CANCELLED)->count();
        $noShowCount = $appointments->where('status', \App\Models\Appointment::STATUS_NO_SHOW)->count();

        $allDoctors = \App\Models\Doctor::with('specialty')->where('is_active', true)->orderBy('name')->get();
        $doctorWorkloads = [];

        foreach ($allDoctors as $doc) {
            $docApts = $appointments->where('doctor_id', $doc->id);
            if ($docApts->isNotEmpty() || $timeFrame === '24h') {
                $doctorWorkloads[] = [
                    'doctor' => $doc,
                    'total_assigned' => $docApts->count(),
                    'completed' => $docApts->where('status', \App\Models\Appointment::STATUS_COMPLETED)->count(),
                    'in_consultation' => $docApts->where('status', \App\Models\Appointment::STATUS_IN_CONSULTATION)->count(),
                    'checked_in' => $docApts->where('status', \App\Models\Appointment::STATUS_CHECKED_IN)->count(),
                    'scheduled' => $docApts->where('status', \App\Models\Appointment::STATUS_SCHEDULED)->count(),
                    'cancelled' => $docApts->where('status', \App\Models\Appointment::STATUS_CANCELLED)->count(),
                    'no_show' => $docApts->where('status', \App\Models\Appointment::STATUS_NO_SHOW)->count(),
                ];
            }
        }
        usort($doctorWorkloads, fn($a, $b) => $b['total_assigned'] <=> $a['total_assigned']);

        $specialtyComparison = [];
        $groupedBySpecialty = $appointments->groupBy(fn($apt) => $apt->doctor->specialty->name ?? 'General');

        foreach ($groupedBySpecialty as $specialtyName => $items) {
            $specialtyComparison[] = [
                'name' => $specialtyName,
                'total' => $items->count(),
                'completed' => $items->where('status', \App\Models\Appointment::STATUS_COMPLETED)->count(),
                'cancelled' => $items->where('status', \App\Models\Appointment::STATUS_CANCELLED)->count(),
                'percentage' => $totalAppointments > 0 ? round(($items->count() / $totalAppointments) * 100, 1) : 0,
            ];
        }
        usort($specialtyComparison, fn($a, $b) => $b['total'] <=> $a['total']);

        $clinicName = \App\Models\ClinicSetting::get('clinic_name', 'CarePoint Outpatient Clinic');
        $generatedAt = now();

        return view('reports.daily', compact(
            'reportDate', 'timeFrame', 'startTime', 'endTime', 'appointments',
            'totalAppointments', 'completedCount', 'inConsultationCount', 'checkedInCount',
            'scheduledCount', 'cancelledCount', 'noShowCount', 'doctorWorkloads',
            'specialtyComparison', 'clinicName', 'generatedAt'
        ));
    }

    public function downloadDailyCsv(\Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $reportDate = $request->input('date', now()->toDateString());
        $timeFrame = $request->input('time_frame', '24h');
        $startTime = $request->input('start_time', '00:00');
        $endTime = $request->input('end_time', '23:59');

        $query = \App\Models\Appointment::with(['patient', 'doctor.specialty'])
            ->whereDate('appointment_date', $reportDate);

        if ($timeFrame === 'custom') {
            $query->where('appointment_time', '>=', $startTime . ':00')
                  ->where('appointment_time', '<=', $endTime . ':00');
        }

        $appointments = $query->orderBy('appointment_time', 'asc')->get();
        $filename = "clinic_report_${reportDate}_${timeFrame}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($appointments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Appointment Code', 'Date', 'Time', 'Status', 'Patient ID', 'Patient Name', 'Age', 'Sex', 'Phone', 'Doctor Code', 'Doctor Name', 'Specialty', 'Room', 'Complaint', 'Report Generated At']);
            $generatedNow = now()->format('Y-m-d H:i:s');
            foreach ($appointments as $apt) {
                fputcsv($handle, [
                    $apt->appointment_code, $apt->appointment_date->format('Y-m-d'), $apt->formatted_time,
                    $apt->status, $apt->patient->patient_code ?? '', $apt->patient->name ?? '',
                    $apt->patient->effective_age ?? '', $apt->patient->sex ?? '', $apt->patient->phone ?? '',
                    $apt->doctor->doctor_code ?? '', $apt->doctor->name ?? '', $apt->doctor->specialty->name ?? '',
                    $apt->doctor->consultation_room ?? '', $apt->primary_complaint, $generatedNow
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
