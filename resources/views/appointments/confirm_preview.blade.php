@extends('layouts.app')

@section('title', 'Confirm Appointment Allocation')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card-custom shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-shield-check text-success fs-4"></i>
                        <h5 class="fw-bold mb-0 text-dark">Step 6: Confirm Doctor & Time Slot Allocation</h5>
                    </div>
                    <p class="text-muted small mb-0">Please verify the proposed appointment allocation details before final commitment to the database.</p>
                </div>

                <div class="card-body p-4">
                    <!-- Alternative Slot Alert Notice -->
                    @if($allocation['is_alternative_slot'])
                        <div class="alert alert-warning border-warning d-flex align-items-start gap-3 mb-4 p-3 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0"></i>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Alternative Time Slot Allocated!</h6>
                                <p class="small mb-0 text-secondary">
                                    The exact requested time (<strong>{{ \Carbon\Carbon::parse($allocation['requested_date'])->format('M d, Y') }} at {{ \Carbon\Carbon::parse($allocation['requested_time'])->format('h:i A') }}</strong>) was occupied or outside doctor availability.
                                    The scheduling engine has automatically identified and reserved the <strong>earliest suitable alternative slot</strong>.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success border-success d-flex align-items-center gap-2 mb-4 p-3 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div class="small fw-semibold text-dark">
                                Great! The exact requested appointment date and time slot is available and matched.
                            </div>
                        </div>
                    @endif

                    @if(!empty($allocation['prediction_data']))
                        <div class="card border-primary-subtle bg-primary bg-opacity-10 mb-4 p-3 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-primary-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
                                    <h6 class="fw-bold mb-0 text-primary">Clinical Triage & Specialty Routing</h6>
                                </div>
                                <span class="badge bg-{{ $allocation['prediction_data']['confidence'] === 'High' ? 'success' : ($allocation['prediction_data']['confidence'] === 'Medium' ? 'info' : 'warning') }}">
                                    {{ $allocation['prediction_data']['confidence'] }} Confidence
                                </span>
                            </div>

                            @if(!empty($allocation['prediction_data']['is_urgent']))
                                <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                    <div>
                                        <strong>Emergency Red Flag Detected:</strong> {{ $allocation['prediction_data']['urgent_instructions'] ?? 'Emergency clinical safeguard protocol active.' }}
                                    </div>
                                </div>
                            @endif

                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted d-block">Diagnosed / Predicted Specialty:</span>
                                    <strong class="text-dark">{{ $allocation['prediction_data']['predicted_specialty_name'] ?? 'General Medicine' }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Routing Status:</span>
                                    @if(!empty($allocation['prediction_data']['is_override']))
                                        <span class="badge bg-warning text-dark">Staff Override Applied</span>
                                        @if(!empty($allocation['prediction_data']['override_reason']))
                                            <div class="text-muted small mt-1">Reason: "{{ $allocation['prediction_data']['override_reason'] }}"</div>
                                        @endif
                                    @else
                                        <span class="badge bg-success">Algorithmic Match Accepted</span>
                                    @endif
                                </div>
                                <div class="col-12 mt-2">
                                    <span class="text-muted d-block">Engine Match Analysis:</span>
                                    <span class="text-secondary">{{ $allocation['prediction_data']['explanation'] ?? 'Standard triage rule matched.' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Proposed Appointment Details Grid -->
                    <div class="bg-light p-3 rounded-3 border mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar-event text-primary me-1"></i> Allocation Summary
                        </h6>

                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Patient Name</small>
                                <span class="fw-bold text-dark">{{ $allocation['patient_name'] }}</span>
                                <small class="text-muted d-block font-monospace">{{ $allocation['patient_code'] }}</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Assigned Doctor</small>
                                <span class="fw-bold text-primary">{{ $allocation['doctor_name'] }}</span>
                                <small class="text-muted d-block">{{ $allocation['doctor_specialty'] }}</small>
                            </div>

                            <div class="col-6">
                                <small class="text-muted d-block">Consultation Room</small>
                                <span class="badge bg-white text-dark border fs-6">{{ $allocation['consultation_room'] }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Allocation Mode</small>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $allocation['booking_mode'] }}</span>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <!-- Requested vs Assigned Comparison -->
                            <div class="col-6">
                                <div class="p-2 border rounded bg-white">
                                    <small class="text-muted fw-semibold d-block">Original Requested Time</small>
                                    <div class="fw-semibold text-secondary">
                                        {{ \Carbon\Carbon::parse($allocation['requested_date'])->format('M d, Y') }}
                                    </div>
                                    <div class="text-secondary small">
                                        {{ \Carbon\Carbon::parse($allocation['requested_time'])->format('h:i A') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2 border rounded {{ $allocation['is_alternative_slot'] ? 'border-warning bg-warning-subtle' : 'border-success bg-success-subtle' }}">
                                    <small class="fw-bold d-block {{ $allocation['is_alternative_slot'] ? 'text-warning-emphasis' : 'text-success-emphasis' }}">
                                        Actual Assigned Slot
                                    </small>
                                    <div class="fw-bold text-dark">
                                        {{ \Carbon\Carbon::parse($allocation['assigned_date'])->format('M d, Y') }}
                                    </div>
                                    <div class="fw-bold text-dark small">
                                        {{ \Carbon\Carbon::parse($allocation['assigned_time'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($allocation['end_time'])->format('h:i A') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <small class="text-muted d-block">Primary Complaint</small>
                                <div class="text-dark small bg-white p-2 border rounded">
                                    {{ $allocation['primary_complaint'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Confirmation Action Form (Executes Step 7 with Concurrency Lock) -->
                    <form method="POST" action="{{ route('appointments.confirm') }}">
                        @csrf
                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <a href="{{ route('appointments.create', ['patient_id' => $allocation['patient_id'] ?? null]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Change Selection
                            </a>
                            <button type="submit" class="btn btn-cms-primary px-4 py-2 shadow-sm fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Confirm & Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
