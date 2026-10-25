<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Services\DoctorAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function __construct(
        protected DoctorAvailabilityService $availabilityService
    ) {}

    /**
     * Display all doctors.
     */
    public function index(Request $request)
    {
        $query = Doctor::with('specialty');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('doctor_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('consultation_room', 'like', "%{$search}%");
            });
        }

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->input('specialty_id'));
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $doctors = $query->orderBy('name')->paginate(15)->withQueryString();
        $specialties = Specialty::orderBy('name')->get();

        return view('doctors.index', compact('doctors', 'specialties'));
    }

    /**
     * Show add doctor form.
     */
    public function create()
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('doctors.create', compact('specialties'));
    }

    /**
     * Store new doctor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'email' => ['required', 'email', 'max:150', 'unique:doctors,email'],
            'phone' => ['required', 'string', 'max:30'],
            'qualifications' => ['required', 'string', 'max:250'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'consultation_room' => ['required', 'string', 'max:100'],
            'consultation_duration' => ['required', 'integer', Rule::in([10, 15, 20, 30, 45, 60])],
            'bio' => ['nullable', 'string'],
        ]);

        $count = Doctor::count() + 1;
        $validated['doctor_code'] = 'DOC-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $validated['is_active'] = true;

        $doctor = Doctor::create($validated);

        return redirect()->route('schedules.manage', $doctor)
            ->with('success', "Doctor {$doctor->name} added successfully. Please configure their weekly working schedule.");
    }

    /**
     * View doctor profile and duty details.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['specialty', 'schedules', 'breaks', 'unavailabilities']);
        $today = Carbon::today();
        $now = Carbon::now();

        $status = $this->availabilityService->getDoctorCurrentStatus($doctor, $now);

        $todayAppointments = $doctor->appointments()
            ->with('patient')
            ->whereDate('appointment_date', $today->toDateString())
            ->orderBy('appointment_time')
            ->get();

        $upcomingAppointments = $doctor->appointments()
            ->with('patient')
            ->where('appointment_date', '>=', $today->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        return view('doctors.show', compact('doctor', 'status', 'todayAppointments', 'upcomingAppointments'));
    }

    /**
     * Edit doctor form.
     */
    public function edit(Doctor $doctor)
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('doctors.edit', compact('doctor', 'specialties'));
    }

    /**
     * Update doctor.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'email' => ['required', 'email', 'max:150', Rule::unique('doctors')->ignore($doctor->id)],
            'phone' => ['required', 'string', 'max:30'],
            'qualifications' => ['required', 'string', 'max:250'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'consultation_room' => ['required', 'string', 'max:100'],
            'consultation_duration' => ['required', 'integer', Rule::in([10, 15, 20, 30, 45, 60])],
            'is_active' => ['required', 'boolean'],
            'bio' => ['nullable', 'string'],
        ]);

        $doctor->update($validated);

        return redirect()->route('doctors.show', $doctor)
            ->with('success', "Doctor {$doctor->name} updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Doctor $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);

        $statusStr = $doctor->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('info', "Doctor {$doctor->name} has been {$statusStr}.");
    }
}
