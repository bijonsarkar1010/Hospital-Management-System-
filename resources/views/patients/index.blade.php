@extends('layouts.app')

@section('title', 'Patient Directory')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Patient Directory</h4>
            <p class="text-muted small mb-0">Search registered clinic patients, view medical profiles, and manage appointments.</p>
        </div>
        <a href="{{ route('patients.create') }}" class="btn btn-sm btn-cms-primary shadow-sm px-3">
            <i class="bi bi-person-plus me-1"></i> Register New Patient
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card-custom mb-4 p-3">
        <form method="GET" action="{{ route('patients.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search by patient name, ID (e.g. PAT-2026-0001), phone, or email...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="sex" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ request('sex') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="blood_group" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Blood Groups</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'] as $bg)
                        <option value="{{ $bg }}" {{ request('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                <button type="submit" class="btn btn-sm btn-cms-primary px-3">Search</button>
                @if(request('search') || request('sex') || request('blood_group'))
                    <a href="{{ route('patients.index') }}" class="btn btn-sm btn-light border">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Patient Directory Table -->
    <div class="card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Patient ID</th>
                            <th>Name & Demographics</th>
                            <th>Contact</th>
                            <th>Blood & Vitals</th>
                            <th>Registered On</th>
                            <th>Last Visit</th>
                            <th>Upcoming</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('patients.show', $p) }}" class="fw-bold text-dark font-monospace text-decoration-none">
                                        {{ $p->patient_code }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $p) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $p->name }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <span>{{ $p->effective_age }} yrs</span> • 
                                        <span>{{ $p->sex }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $p->phone }}</div>
                                    @if($p->email)
                                        <small class="text-muted">{{ $p->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border me-1">{{ $p->blood_group ?? 'Unknown' }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.76rem;">{{ $p->blood_pressure_formatted }}</small>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $p->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    @if($p->lastAppointment)
                                        <div class="small fw-medium">{{ $p->lastAppointment->formatted_date }}</div>
                                        <small class="text-muted">{{ $p->lastAppointment->doctor->name ?? '' }}</small>
                                    @else
                                        <span class="text-muted small">None yet</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->upcomingAppointment)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                            {{ $p->upcomingAppointment->formatted_date }} ({{ $p->upcomingAppointment->formatted_time }})
                                        </span>
                                    @else
                                        <span class="text-muted small">None scheduled</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('appointments.create', ['patient_id' => $p->id]) }}" class="btn btn-sm btn-light border py-1 px-2 text-primary" title="Book Appointment">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>
                                        <a href="{{ route('patients.show', $p) }}" class="btn btn-sm btn-light border py-1 px-2" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('patients.edit', $p) }}" class="btn btn-sm btn-light border py-1 px-2" title="Edit Patient">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary-subtle"></i>
                                    No patients found matching the search criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($patients->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $patients->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
