@extends('layouts.app')

@section('title', 'Manage Schedule - ' . $doctor->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">Schedule & Duty Configuration: {{ $doctor->name }}</h4>
                <span class="badge bg-secondary font-monospace">{{ $doctor->doctor_code }}</span>
            </div>
            <p class="text-muted small mb-0">{{ $doctor->specialty->name }} • {{ $doctor->consultation_room }} • Duration: {{ $doctor->consultation_duration }} mins</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-person me-1"></i> Doctor Profile
            </a>
            <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-arrow-left me-1"></i> Duty Roster
            </a>
        </div>
    </div>

    <!-- Conflict Warning Alert (Section 8: Changing schedule must detect conflicts) -->
    @if(session('error_conflicts'))
        <div class="alert alert-danger border-danger shadow-sm mb-4" role="alert">
            <h6 class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Schedule Change Prevented: Conflicting Appointments Found!</h6>
            <p class="small mb-2">The requested schedule modifications would invalidate or conflict with existing booked appointments:</p>
            <ul class="small mb-0 ps-3">
                @foreach(session('error_conflicts') as $conflict)
                    <li>{{ $conflict }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- 1. Weekly Recurring Working Hours -->
        <div class="col-12 col-lg-8">
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-primary"></i>
                        <span class="fw-bold">Weekly Working Hours</span>
                    </div>
                    <span class="badge bg-info-subtle text-info small">Dynamic Slot Engine</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('schedules.update', $doctor) }}">
                        @csrf

                        @php $schedByDay = $doctor->schedules->keyBy('day_of_week'); @endphp

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-3 small">
                                <thead class="table-light text-muted">
                                    <tr>
                                        <th style="width: 140px;">Weekday</th>
                                        <th style="width: 120px;">Duty Status</th>
                                        <th>Shift Start</th>
                                        <th>Shift End</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $dayNum => $dayName)
                                        @php
                                            $sch = $schedByDay->get($dayNum);
                                            $isWorking = $sch && $sch->is_available;
                                            $startTime = $sch ? \Carbon\Carbon::parse($sch->start_time)->format('H:i') : '09:00';
                                            $endTime = $sch ? \Carbon\Carbon::parse($sch->end_time)->format('H:i') : '17:00';
                                        @endphp
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $dayName }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="schedules[{{ $dayNum }}][active]" value="1" id="switch{{ $dayNum }}" {{ $isWorking ? 'checked' : '' }} onchange="toggleDayRow({{ $dayNum }})">
                                                    <label class="form-check-label text-muted" for="switch{{ $dayNum }}" id="label{{ $dayNum }}">
                                                        {{ $isWorking ? 'On Duty' : 'Off' }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="time" name="schedules[{{ $dayNum }}][start_time]" id="start{{ $dayNum }}" value="{{ $startTime }}" class="form-control form-control-sm" {{ !$isWorking ? 'disabled' : '' }}>
                                            </td>
                                            <td>
                                                <input type="time" name="schedules[{{ $dayNum }}][end_time]" id="end{{ $dayNum }}" value="{{ $endTime }}" class="form-control form-control-sm" {{ !$isWorking ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Slots are dynamically generated by consultation duration ({{ $doctor->consultation_duration }} min).</small>
                            <button type="submit" class="btn btn-cms-primary px-4 py-2 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Save Working Hours
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Break Periods & Leave Management (Right Column) -->
        <div class="col-12 col-lg-4">
            <!-- Scheduled Breaks -->
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-cup-hot text-warning"></i>
                        <span class="fw-bold">Scheduled Breaks</span>
                    </div>
                </div>

                <div class="card-body p-3">
                    @forelse($doctor->breaks as $brk)
                        <div class="p-2 border rounded bg-light mb-2 d-flex align-items-center justify-content-between small">
                            <div>
                                <span class="fw-bold text-dark">{{ $brk->label }}</span>
                                <div class="text-muted">
                                    {{ \Carbon\Carbon::parse($brk->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($brk->end_time)->format('h:i A') }}
                                    <small>({{ $brk->day_of_week !== null ? \App\Models\DoctorSchedule::getDayName($brk->day_of_week) : 'All days' }})</small>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('schedules.breaks.delete', $brk) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Delete break">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="small text-muted mb-3">No break periods set for this doctor.</p>
                    @endforelse

                    <!-- Add Break Form -->
                    <div class="border-top pt-3 mt-2">
                        <h6 class="small fw-bold text-dark mb-2">Add New Break:</h6>
                        <form method="POST" action="{{ route('schedules.breaks.add', $doctor) }}">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="label" class="form-control form-control-sm" placeholder="Label (e.g. Lunch Break, Afternoon Tea)" required>
                            </div>
                            <div class="row g-1 mb-2">
                                <div class="col-6">
                                    <input type="time" name="start_time" class="form-control form-control-sm" value="13:00" required>
                                </div>
                                <div class="col-6">
                                    <input type="time" name="end_time" class="form-control form-control-sm" value="14:00" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <select name="day_of_week" class="form-select form-select-sm">
                                    <option value="">Every Working Day</option>
                                    @foreach($days as $num => $name)
                                        <option value="{{ $num }}">{{ $name }} Only</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-plus-lg me-1"></i> Add Break
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Leave & Holidays -->
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-x text-danger"></i>
                    <span class="fw-bold">Leave & Unavailability</span>
                </div>

                <div class="card-body p-3">
                    @forelse($doctor->unavailabilities as $un)
                        <div class="p-2 border rounded bg-danger-subtle text-danger mb-2 d-flex align-items-center justify-content-between small">
                            <div>
                                <span class="fw-bold text-dark text-capitalize">{{ $un->type }}: {{ $un->reason ?? 'Unavailable' }}</span>
                                <div class="text-muted">
                                    {{ $un->start_date->format('M d, Y') }} to {{ $un->end_date->format('M d, Y') }}
                                    @if($un->start_time)
                                        ({{ \Carbon\Carbon::parse($un->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($un->end_time)->format('h:i A') }})
                                    @else
                                        (Full Day)
                                    @endif
                                </div>
                            </div>
                            <form method="POST" action="{{ route('schedules.leaves.delete', $un) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger border-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="small text-muted mb-3">No leaves or blocked periods recorded.</p>
                    @endforelse

                    <!-- Add Leave Form -->
                    <div class="border-top pt-3 mt-2">
                        <h6 class="small fw-bold text-dark mb-2">Record Leave / Blocked Period:</h6>
                        <form method="POST" action="{{ route('schedules.leaves.add', $doctor) }}">
                            @csrf
                            <div class="row g-1 mb-2">
                                <div class="col-6">
                                    <label class="form-label small" style="font-size: 0.72rem;">Start Date</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small" style="font-size: 0.72rem;">End Date</label>
                                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="leave">Annual Leave / Vacation</option>
                                    <option value="holiday">Official Holiday</option>
                                    <option value="emergency">Emergency / Medical Leave</option>
                                    <option value="conference">Conference / Training</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason (e.g. Medical Conference)">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-plus-lg me-1"></i> Record Leave
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleDayRow(dayNum) {
        const sw = document.getElementById('switch' + dayNum);
        const lbl = document.getElementById('label' + dayNum);
        const start = document.getElementById('start' + dayNum);
        const end = document.getElementById('end' + dayNum);

        if (sw.checked) {
            lbl.textContent = 'On Duty';
            start.disabled = false;
            end.disabled = false;
        } else {
            lbl.textContent = 'Off';
            start.disabled = true;
            end.disabled = true;
        }
    }
</script>
@endpush
