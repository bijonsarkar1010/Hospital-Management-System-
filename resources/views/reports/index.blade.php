@extends('layouts.app')

@section('title', 'Operational Reports')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Clinic Operational Reports</h4>
            <p class="text-muted small mb-0">Summary of patient registration volumes, physician workloads, and appointment outcomes.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- CSV Export Action -->
            <a href="{{ route('reports.export_csv', request()->all()) }}" class="btn btn-sm btn-outline-success shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Appointments to CSV
            </a>
        </div>
    </div>

    <!-- Date Range & Filter Card -->
    <div class="card-custom mb-4 p-3">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" style="font-size: 0.72rem;">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" style="font-size: 0.72rem;">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" style="font-size: 0.72rem;">Specialty</label>
                <select name="specialty_id" class="form-select form-select-sm">
                    <option value="">All Specialties</option>
                    @foreach($specialties as $sp)
                        <option value="{{ $sp->id }}" {{ $specialtyId == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" style="font-size: 0.72rem;">Doctor</label>
                <select name="doctor_id" class="form-select form-select-sm">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $d)
                        <option value="{{ $d->id }}" {{ $doctorId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1" style="font-size: 0.72rem;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2 d-flex gap-2 align-items-end pt-3">
                <button type="submit" class="btn btn-sm btn-cms-primary w-100">Apply Filters</button>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">Total Appointments</small>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalAppointments) }}</h3>
                <small class="text-secondary" style="font-size: 0.72rem;">In selected period</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">Completed</small>
                <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($completedCount) }}</h3>
                <small class="text-success" style="font-size: 0.72rem;">Consultations done</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">Cancelled</small>
                <h3 class="fw-bold text-danger mb-0 mt-1">{{ number_format($cancelledCount) }}</h3>
                <small class="text-danger" style="font-size: 0.72rem;">Slots released</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">No-Show</small>
                <h3 class="fw-bold text-secondary mb-0 mt-1">{{ number_format($noShowCount) }}</h3>
                <small class="text-secondary" style="font-size: 0.72rem;">Patient absent</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">Scheduled / Active</small>
                <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($scheduledCount) }}</h3>
                <small class="text-primary" style="font-size: 0.72rem;">Upcoming slots</small>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card-custom p-3 text-center">
                <small class="text-muted d-block fw-semibold">New Patients</small>
                <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($newPatientsCount) }}</h3>
                <small class="text-info" style="font-size: 0.72rem;">Registered in range</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- 1. Appointments by Doctor Table -->
        <div class="col-12 col-xl-7">
            <div class="card-custom h-100">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill text-primary"></i>
                        <span class="fw-bold">Physician Workload Breakdown</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="ps-3">Doctor</th>
                                    <th>Specialty</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center text-success">Completed</th>
                                    <th class="text-center text-danger">Cancelled</th>
                                    <th class="text-center text-secondary">No Show</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($doctorStats as $ds)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            {{ $ds->doctor->name ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $ds->doctor->specialty->name ?? '' }}</span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $ds->total }}</td>
                                        <td class="text-center text-success fw-semibold">{{ $ds->completed }}</td>
                                        <td class="text-center text-danger">{{ $ds->cancelled }}</td>
                                        <td class="text-center text-secondary">{{ $ds->no_show }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No appointments found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Appointments by Specialty & Date Summary -->
        <div class="col-12 col-xl-5">
            <!-- Specialty Breakdown -->
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3 text-primary"></i>
                    <span class="fw-bold">Appointments by Specialty</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="ps-3">Specialty</th>
                                    <th class="text-end pe-3">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($specialtyStats as $sp)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">{{ $sp->specialty_name }}</td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-primary rounded-pill px-2 py-1">{{ $sp->total }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">No specialty records.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Date Breakdown (Recent) -->
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-range text-secondary"></i>
                    <span class="fw-bold">Appointments by Date (Summary)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center text-success">Completed</th>
                                    <th class="text-center text-danger">Cancelled</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dateStats as $dt)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">{{ \Carbon\Carbon::parse($dt->appointment_date)->format('M d, Y') }}</td>
                                        <td class="text-center fw-bold">{{ $dt->total }}</td>
                                        <td class="text-center text-success">{{ $dt->completed }}</td>
                                        <td class="text-center text-danger">{{ $dt->cancelled }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No date activity.</td>
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
