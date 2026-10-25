@extends('layouts.app')

@section('title', 'Appointment Details - ' . $appointment->appointment_code)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">Appointment #{{ $appointment->appointment_code }}</h4>
                <span class="{{ $appointment->status_badge_class }} py-1 px-3 rounded-pill fs-6">{{ $appointment->status }}</span>
            </div>
            <p class="text-muted small mb-0">Scheduled on {{ $appointment->appointment_date->format('l, M d, Y') }} at {{ $appointment->formatted_time }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('appointments.slip', $appointment) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="bi bi-printer me-1"></i> Print Slip
            </a>
            @if($appointment->status !== \App\Models\Appointment::STATUS_CANCELLED && $appointment->status !== \App\Models\Appointment::STATUS_COMPLETED)
                <a href="{{ route('appointments.reschedule_form', $appointment) }}" class="btn btn-sm btn-cms-primary shadow-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Reschedule
                </a>
            @endif
            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-12 col-lg-8">
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span class="fw-bold">Consultation Information</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Assigned Doctor</small>
                            <span class="fw-bold fs-6 text-dark">{{ $appointment->doctor->name }}</span>
                            <div class="text-muted small">{{ $appointment->doctor->specialty->name }}</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Consultation Room</small>
                            <span class="badge bg-light text-dark border fs-6">{{ $appointment->doctor->consultation_room }}</span>
                        </div>

                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Appointment Date</small>
                            <span class="fw-semibold text-dark">{{ $appointment->formatted_date }}</span>
                        </div>

                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Time Slot Window</small>
                            <span class="fw-semibold text-dark">{{ $appointment->formatted_time }} - {{ $appointment->formatted_end_time }}</span>
                        </div>

                        <div class="col-12">
                            <small class="text-muted d-block">Primary Complaint / Symptoms</small>
                            <div class="p-3 bg-light rounded border text-dark">
                                {{ $appointment->primary_complaint }}
                            </div>
                        </div>

                        @if($appointment->cancellation_reason)
                            <div class="col-12">
                                <small class="text-danger fw-bold d-block">Cancellation Reason</small>
                                <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded">
                                    {{ $appointment->cancellation_reason }}
                                </div>
                            </div>
                        @endif

                        @if($appointment->consultation_notes)
                            <div class="col-12">
                                <small class="text-muted d-block">Doctor Consultation Notes</small>
                                <div class="p-3 bg-light rounded border text-dark">
                                    {{ $appointment->consultation_notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Clinical Routing & Symptom Prediction Audit Card -->
            @if($appointment->prediction)
                <div class="card-custom mb-4 border-primary-subtle">
                    <div class="card-header-clean bg-primary bg-opacity-10 d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
                            <span class="fw-bold text-dark">Clinical Routing & Symptom Prediction Record</span>
                        </div>
                        <span class="badge bg-{{ $appointment->prediction->confidence === 'High' ? 'success' : ($appointment->prediction->confidence === 'Medium' ? 'info' : 'warning') }}">
                            {{ $appointment->prediction->confidence }} Confidence
                        </span>
                    </div>

                    <div class="card-body p-4">
                        @if($appointment->prediction->is_urgent)
                            <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <div>
                                    <strong>Emergency Safeguard Flagged:</strong> Patient symptoms matched emergency clinical criteria.
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Algorithmic Predicted Specialty</small>
                                <strong class="text-dark fs-6">{{ $appointment->prediction->predictedSpecialty->name ?? 'General Medicine' }}</strong>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Confirmed Specialty Allocated</small>
                                <strong class="text-primary fs-6">{{ $appointment->prediction->confirmedSpecialty->name ?? $appointment->doctor->specialty->name }}</strong>
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Override Status</small>
                                @if($appointment->prediction->is_override)
                                    <span class="badge bg-warning text-dark">Staff Override Applied</span>
                                    @if($appointment->prediction->override_reason)
                                        <div class="text-muted small mt-1">Reason: "{{ $appointment->prediction->override_reason }}"</div>
                                    @endif
                                @else
                                    <span class="badge bg-success">Standard Diagnostic Allocation</span>
                                @endif
                            </div>

                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Clinical Urgency Classification</small>
                                @if($appointment->prediction->is_urgent)
                                    <span class="badge bg-danger">Critical / Emergency</span>
                                @else
                                    <span class="badge bg-light text-dark border">Routine Outpatient</span>
                                @endif
                            </div>

                            <div class="col-12">
                                <small class="text-muted d-block">Decision & Rule Match Rationale</small>
                                <div class="p-2 bg-light rounded border small text-secondary">
                                    {{ $appointment->prediction->explanation ?? 'Symptom matching rule resolved.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status Update Form -->
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat text-primary"></i>
                    <span class="fw-bold">Update Appointment Status</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('appointments.update_status', $appointment) }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    @foreach(\App\Models\Appointment::getStatuses() as $st)
                                        <option value="{{ $st }}" {{ $appointment->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Clinical / Reception Notes (Optional)</label>
                                <input type="text" name="consultation_notes" value="{{ $appointment->consultation_notes }}" class="form-control form-control-sm" placeholder="Notes on consultation or patient arrival...">
                            </div>
                            <div class="col-12 col-md-2 mt-md-4">
                                <button type="submit" class="btn btn-sm btn-cms-primary w-100">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Patient Quick Summary (Right Column) -->
        <div class="col-12 col-lg-4">
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-person text-primary"></i>
                    <span class="fw-bold">Patient Details</span>
                </div>
                <div class="card-body p-3">
                    <h5 class="fw-bold text-dark mb-1">{{ $appointment->patient->name }}</h5>
                    <span class="badge bg-secondary font-monospace mb-3">{{ $appointment->patient->patient_code }}</span>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-semibold text-dark">{{ $appointment->patient->phone }}</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Age / Sex</small>
                        <span class="text-dark">{{ $appointment->patient->effective_age }} years • {{ $appointment->patient->sex }}</span>
                    </div>

                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted d-block">Blood Group</small>
                        <span class="badge bg-light text-dark border">{{ $appointment->patient->blood_group ?? 'Unknown' }}</span>
                    </div>

                    <div>
                        <a href="{{ route('patients.show', $appointment->patient) }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-person-vcard me-1"></i> View Full Patient Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
