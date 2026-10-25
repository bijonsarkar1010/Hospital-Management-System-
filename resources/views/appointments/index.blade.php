@extends('layouts.app')

@section('title', 'Appointment Management')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Appointment Management</h4>
            <p class="text-muted small mb-0">Search, monitor patient check-ins, reschedule, and manage clinic appointments.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-cms-primary shadow-sm px-3">
                <i class="bi bi-calendar-plus me-1"></i> Book New Appointment
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom mb-4 p-3">
        <form method="GET" action="{{ route('appointments.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="ID (APT-...), patient, phone...">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="specialty_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Specialties</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" {{ request('doctor_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-1 text-md-end">
                @if(request('search') || request('specialty_id') || request('doctor_id') || request('date') || request('status'))
                    <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border w-100" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @else
                    <button type="submit" class="btn btn-sm btn-light border w-100">
                        <i class="bi bi-filter"></i>
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Appointment Management Table -->
    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Code & Date</th>
                            <th>Time Window</th>
                            <th>Patient Information</th>
                            <th>Assigned Doctor</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Mode</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $apt)
                            <tr>
                                <td>
                                    <a href="{{ route('appointments.show', $apt) }}" class="fw-bold text-dark font-monospace text-decoration-none">
                                        {{ $apt->appointment_code }}
                                    </a>
                                    <small class="text-muted d-block">{{ $apt->formatted_date }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $apt->formatted_time }}</span>
                                    <small class="text-muted d-block">to {{ $apt->formatted_end_time }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $apt->patient) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $apt->patient->name }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <span>{{ $apt->patient->effective_age }} yrs</span> • 
                                        <span>{{ $apt->patient->sex }}</span> • 
                                        <span>{{ $apt->patient->phone }}</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('doctors.show', $apt->doctor) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $apt->doctor->name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $apt->doctor->specialty->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $apt->doctor->consultation_room }}</span>
                                </td>
                                <td>
                                    <span class="{{ $apt->status_badge_class }} py-1 px-2 rounded-pill small">
                                        {{ $apt->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase" style="font-size: 0.7rem;">
                                        {{ $apt->booking_mode }}
                                    </span>
                                    @if($apt->is_alternative_slot)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-block mt-1" style="font-size: 0.65rem;">
                                            Alternative
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Status Transition Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li class="dropdown-header small">Change Status</li>
                                                @foreach($statuses as $st)
                                                    @if($st !== $apt->status)
                                                        <li>
                                                            <form method="POST" action="{{ route('appointments.update_status', $apt) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $st }}">
                                                                <button type="submit" class="dropdown-item small">
                                                                    Mark as {{ $st }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>

                                        <!-- Print Slip -->
                                        <a href="{{ route('appointments.slip', $apt) }}" class="btn btn-sm btn-light border py-1 px-2" target="_blank" title="Print Slip">
                                            <i class="bi bi-printer text-secondary"></i>
                                        </a>

                                        <!-- Detail & Actions -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border py-1 px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item small" href="{{ route('appointments.show', $apt) }}">
                                                        <i class="bi bi-eye me-2"></i> View Details
                                                    </a>
                                                </li>
                                                @if($apt->status !== \App\Models\Appointment::STATUS_CANCELLED && $apt->status !== \App\Models\Appointment::STATUS_COMPLETED)
                                                    <li>
                                                        <a class="dropdown-item small" href="{{ route('appointments.reschedule_form', $apt) }}">
                                                            <i class="bi bi-arrow-repeat me-2 text-primary"></i> Reschedule
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('appointments.cancel', $apt) }}" onsubmit="return confirm('Cancel appointment #{{ $apt->appointment_code }}? The slot will be released.')">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item small text-danger">
                                                                <i class="bi bi-x-circle me-2"></i> Cancel Appointment
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary-subtle"></i>
                                    No appointments found matching the current search filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($appointments->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $appointments->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
