@extends('layouts.app')

@section('title', 'Doctor Profile - ' . $doctor->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">{{ $doctor->name }}</h4>
                <span class="badge bg-secondary font-monospace">{{ $doctor->doctor_code }}</span>
                <span class="{{ $status['badge_class'] }} py-1 px-2 rounded-pill small">{{ $status['status_label'] }}</span>
            </div>
            <p class="text-muted small mb-0">{{ $doctor->specialty->name }} • {{ $doctor->consultation_room }} • Duration: {{ $doctor->consultation_duration }} mins</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('schedules.manage', $doctor) }}" class="btn btn-sm btn-cms-primary shadow-sm px-3">
                <i class="bi bi-clock me-1"></i> Manage Working Schedule
            </a>
            <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit Doctor
            </a>
            <form method="POST" action="{{ route('doctors.toggle_status', $doctor) }}">
                @csrf
                <button type="submit" class="btn btn-sm {{ $doctor->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                    {{ $doctor->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Doctor Info & Weekly Working Hours -->
        <div class="col-12 col-lg-5">
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span class="fw-bold">Doctor Profile Information</span>
                </div>
                <div class="card-body p-3">
                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Specialty & Department</small>
                        <span class="fw-bold text-dark">{{ $doctor->specialty->name }}</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Qualifications</small>
                        <span class="text-dark">{{ $doctor->qualifications }}</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Clinical Experience</small>
                        <span class="text-dark">{{ $doctor->experience_years }} years of clinical practice</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Consultation Room</small>
                        <span class="badge bg-light text-dark border fs-6">{{ $doctor->consultation_room }}</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Slot Duration</small>
                        <span class="fw-semibold text-dark">{{ $doctor->consultation_duration }} minutes per appointment</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Contact</small>
                        <div><i class="bi bi-telephone text-secondary me-1"></i> {{ $doctor->phone }}</div>
                        <div><i class="bi bi-envelope text-secondary me-1"></i> {{ $doctor->email }}</div>
                    </div>

                    <div>
                        <small class="text-muted d-block">Bio / Overview</small>
                        <p class="small text-secondary mb-0">{{ $doctor->bio ?? 'No additional biography provided.' }}</p>
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule Table -->
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-week text-primary"></i>
                        <span class="fw-bold">Recurring Weekly Hours</span>
                    </div>
                    <a href="{{ route('schedules.manage', $doctor) }}" class="btn btn-sm btn-link text-decoration-none small">Edit</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead>
                            <tr class="text-muted">
                                <th class="ps-3">Day</th>
                                <th>Working Hours</th>
                                <th class="text-end pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $weekdays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'];
                                $schedByDay = $doctor->schedules->keyBy('day_of_week');
                            @endphp
                            @foreach($weekdays as $dayNum => $dayName)
                                @php $sch = $schedByDay->get($dayNum); @endphp
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $dayName }}</td>
                                    <td>
                                        @if($sch && $sch->is_available)
                                            {{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('h:i A') }}
                                        @else
                                            <span class="text-muted">Off Duty</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($sch && $sch->is_available)
                                            <span class="badge bg-success-subtle text-success">On Duty</span>
                                        @else
                                            <span class="badge bg-light text-muted border">Off</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Scheduled Breaks & Leaves -->
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-cup-hot text-warning"></i>
                        <span class="fw-bold">Breaks & Leaves</span>
                    </div>
                    <a href="{{ route('schedules.manage', $doctor) }}" class="btn btn-sm btn-link text-decoration-none small">Manage</a>
                </div>
                <div class="card-body p-3">
                    <h6 class="small fw-bold text-dark mb-2">Daily Breaks:</h6>
                    @forelse($doctor->breaks as $brk)
                        <div class="p-2 border rounded bg-light small mb-2 d-flex justify-content-between">
                            <span><strong>{{ $brk->label }}:</strong> {{ \Carbon\Carbon::parse($brk->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($brk->end_time)->format('h:i A') }}</span>
                            <span class="text-muted">{{ $brk->day_of_week !== null ? \App\Models\DoctorSchedule::getDayName($brk->day_of_week) : 'Every working day' }}</span>
                        </div>
                    @empty
                        <div class="small text-muted mb-3">No break periods scheduled.</div>
                    @endforelse

                    <h6 class="small fw-bold text-dark mt-3 mb-2">Upcoming Leave / Holidays:</h6>
                    @forelse($doctor->unavailabilities as $un)
                        <div class="p-2 border rounded bg-danger-subtle text-danger small mb-2">
                            <div class="fw-bold">{{ ucfirst($un->type) }}: {{ $un->reason ?? 'Leave' }}</div>
                            <div>{{ $un->start_date->format('M d, Y') }} to {{ $un->end_date->format('M d, Y') }}</div>
                        </div>
                    @empty
                        <div class="small text-muted">No upcoming leave scheduled.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Today's Appointments & Upcoming -->
        <div class="col-12 col-lg-7">
            <!-- Today's Schedule -->
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-day text-primary"></i>
                        <span class="fw-bold">Today's Appointment Schedule ({{ now()->format('M d, Y') }})</span>
                    </div>
                    <span class="badge bg-primary">{{ $todayAppointments->count() }} appointments</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Primary Complaint</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Slip</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayAppointments as $apt)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $apt->formatted_time }}</span>
                                            <small class="text-muted d-block">{{ $apt->formatted_end_time }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $apt->patient->name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $apt->patient->phone }}</small>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $apt->primary_complaint }}</span>
                                        </td>
                                        <td>
                                            <span class="{{ $apt->status_badge_class }} py-1 px-2 rounded-pill small">
                                                {{ $apt->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('appointments.slip', $apt) }}" class="btn btn-sm btn-light border py-1 px-2" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No appointments booked for this doctor today.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-event text-secondary"></i>
                        <span class="fw-bold">Next Upcoming Appointments</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $upcomingAppointments->count() }} scheduled</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient</th>
                                    <th>Primary Complaint</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingAppointments as $apt)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $apt->formatted_date }}</span>
                                            <small class="text-muted d-block">{{ $apt->formatted_time }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('patients.show', $apt->patient) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $apt->patient->name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $apt->patient->phone }}</small>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $apt->primary_complaint }}</span>
                                        </td>
                                        <td>
                                            <span class="{{ $apt->status_badge_class }} py-1 px-2 rounded-pill small">
                                                {{ $apt->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No future appointments scheduled.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
