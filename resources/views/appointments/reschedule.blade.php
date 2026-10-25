@extends('layouts.app')

@section('title', 'Reschedule Appointment - ' . $appointment->appointment_code)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Reschedule Appointment #{{ $appointment->appointment_code }}</h4>
            <p class="text-muted small mb-0">Select a new date and time for patient <strong>{{ $appointment->patient->name }}</strong>.</p>
        </div>
        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Cancel
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat text-primary fs-5"></i>
                    <span class="fw-bold">New Slot Allocation</span>
                </div>

                <div class="card-body p-4">
                    <div class="bg-light p-3 rounded border mb-4">
                        <small class="text-muted d-block">Current Scheduled Slot:</small>
                        <div class="fw-bold text-dark">{{ $appointment->formatted_date }} at {{ $appointment->formatted_time }}</div>
                        <div class="text-secondary small">{{ $appointment->doctor->name }} ({{ $appointment->doctor->specialty->name }}) - {{ $appointment->doctor->consultation_room }}</div>
                    </div>

                    <form method="POST" action="{{ route('appointments.reschedule', $appointment) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Doctor (Optional Change)</label>
                            <select name="preferred_doctor_id" class="form-select form-select-sm">
                                <option value="{{ $appointment->doctor_id }}">Keep Current: {{ $appointment->doctor->name }} ({{ $appointment->doctor->specialty->name }})</option>
                                @foreach($doctors as $d)
                                    @if($d->id !== $appointment->doctor_id)
                                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->specialty->name }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">New Requested Date <span class="text-danger">*</span></label>
                                <input type="date" name="requested_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">New Requested Time <span class="text-danger">*</span></label>
                                <input type="time" name="requested_time" class="form-control form-control-sm" value="{{ now()->addHour()->format('H:00') }}" required>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 small mb-4">
                            <i class="bi bi-info-circle me-1"></i> Rescheduling will automatically verify doctor availability, release the old slot, and book the new slot.
                        </div>

                        <button type="submit" class="btn btn-cms-primary w-100 py-2 shadow-sm fw-bold">
                            Verify & Confirm Reschedule <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
