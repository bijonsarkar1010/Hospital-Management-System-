@extends('layouts.app')

@section('title', 'Weekly Duty Roster & Doctor Schedules')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Weekly Doctor Duty Roster</h4>
            <p class="text-muted small mb-0">Master schedule of physician working hours, consultation rooms, and duty coverage across all 7 days.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 fs-6">Today: {{ now()->format('l, M d') }}</span>
        </div>
    </div>

    <div class="card-custom">
        <div class="card-header-clean d-flex align-items-center justify-content-between">
            <span class="fw-bold">Physician Schedule Grid</span>
            <small class="text-muted">Total active doctors: {{ $doctors->count() }}</small>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-3" style="min-width: 200px;">Doctor & Specialty</th>
                            <th style="min-width: 90px;">Room</th>
                            @foreach($days as $dayNum => $dayName)
                                <th style="min-width: 120px;" class="{{ now()->dayOfWeek === $dayNum ? 'table-primary border-primary text-primary fw-bold' : '' }}">
                                    {{ $dayName }}
                                    @if(now()->dayOfWeek === $dayNum)
                                        <span class="badge bg-primary text-white d-block mt-1" style="font-size: 0.65rem;">TODAY</span>
                                    @endif
                                </th>
                            @endforeach
                            <th style="min-width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($doctors as $d)
                            @php
                                $schedByDay = $d->schedules->keyBy('day_of_week');
                                $hasActiveLeave = $d->unavailabilities->isNotEmpty();
                            @endphp
                            <tr>
                                <td class="text-start ps-3">
                                    <a href="{{ route('doctors.show', $d) }}" class="fw-bold text-dark text-decoration-none d-block">
                                        {{ $d->name }}
                                    </a>
                                    <small class="text-muted">{{ $d->specialty->name }} ({{ $d->consultation_duration }}m)</small>
                                    @if($hasActiveLeave)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-block mt-1" style="font-size: 0.68rem;">
                                            Leave: {{ $d->unavailabilities->first()->reason ?? 'Leave' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $d->consultation_room }}</span>
                                </td>

                                @foreach($days as $dayNum => $dayName)
                                    @php $sch = $schedByDay->get($dayNum); @endphp
                                    <td class="{{ now()->dayOfWeek === $dayNum ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                                        @if($sch && $sch->is_available)
                                            <span class="fw-semibold text-dark d-block">
                                                {{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}
                                            </span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">On Duty</span>
                                        @else
                                            <span class="text-muted small">--</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td>
                                    <a href="{{ route('schedules.manage', $d) }}" class="btn btn-sm btn-light border py-1 px-2" title="Manage Schedule">
                                        <i class="bi bi-gear text-secondary"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
