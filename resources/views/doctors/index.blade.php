@extends('layouts.app')

@section('title', 'Doctor Directory')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Doctor Directory</h4>
            <p class="text-muted small mb-0">Clinic medical staff, specialties, consultation durations, and duty management.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('roster.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar-week me-1"></i> Duty Roster
            </a>
            <a href="{{ route('doctors.create') }}" class="btn btn-sm btn-cms-primary shadow-sm px-3">
                <i class="bi bi-person-plus me-1"></i> Add Doctor
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom mb-4 p-3">
        <form method="GET" action="{{ route('doctors.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search doctor name, code (DOC-001), room, or email...">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="specialty_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Specialties</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <div class="col-12 col-md-2 text-md-end">
                <button type="submit" class="btn btn-sm btn-cms-primary px-3">Filter</button>
                @if(request('search') || request('specialty_id') || request('status') !== null)
                    <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-light border ms-1">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Doctor Grid / Table -->
    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Doctor ID</th>
                            <th>Doctor Name & Qualifications</th>
                            <th>Specialty</th>
                            <th>Room</th>
                            <th>Duration</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $d)
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-dark">{{ $d->doctor_code }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('doctors.show', $d) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $d->name }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <span>{{ $d->qualifications }}</span> • 
                                        <span>{{ $d->experience_years }} yrs exp</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $d->specialty->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $d->consultation_room }}</span>
                                </td>
                                <td>
                                    <span class="small text-secondary">{{ $d->consultation_duration }} mins</span>
                                </td>
                                <td>
                                    <div>{{ $d->phone }}</div>
                                    <small class="text-muted">{{ $d->email }}</small>
                                </td>
                                <td>
                                    @if($d->is_active)
                                        <span class="badge bg-success-subtle text-success py-1 px-2 rounded-pill small">Active</span>
                                    @else
                                        <span class="badge bg-secondary py-1 px-2 rounded-pill small">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('schedules.manage', $d) }}" class="btn btn-sm btn-light border py-1 px-2 text-primary" title="Configure Schedule & Breaks">
                                            <i class="bi bi-clock"></i>
                                        </a>
                                        <a href="{{ route('doctors.show', $d) }}" class="btn btn-sm btn-light border py-1 px-2" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('doctors.edit', $d) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit Doctor">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    No doctors found matching the query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($doctors->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $doctors->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
