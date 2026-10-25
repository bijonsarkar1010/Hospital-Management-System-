@extends('layouts.app')

@section('title', 'Patient Details - ' . $patient->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark">{{ $patient->name }}</h4>
                <span class="badge bg-secondary font-monospace">{{ $patient->patient_code }}</span>
            </div>
            <p class="text-muted small mb-0">Registered on {{ $patient->created_at->format('M d, Y') }} • Reception Patient Record</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-cms-primary shadow-sm px-3">
                <i class="bi bi-calendar-plus me-1"></i> Book Follow-up Appointment
            </a>
            <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-pencil me-1"></i> Edit Info
            </a>
            <a href="{{ route('patients.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Patient Summary Card -->
        <div class="col-12 col-lg-4">
            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <span class="fw-bold">Demographics & Contact</span>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small">Age / Sex</div>
                        <div class="fw-bold text-dark">{{ $patient->effective_age }} years old • {{ $patient->sex }}</div>
                        @if($patient->date_of_birth)
                            <small class="text-muted">Born {{ $patient->date_of_birth->format('M d, Y') }}</small>
                        @endif
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small">Primary Phone</div>
                        <div class="fw-semibold text-dark">{{ $patient->phone }}</div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small">Email Address</div>
                        <div class="text-dark">{{ $patient->email ?? 'Not provided' }}</div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-muted small">Address</div>
                        <div class="text-dark">{{ $patient->address ?? 'Not provided' }}</div>
                    </div>

                    <div>
                        <div class="text-muted small">Emergency Contact</div>
                        <div class="fw-semibold text-dark">{{ $patient->emergency_contact_name ?? 'None' }}</div>
                        <small class="text-muted">{{ $patient->emergency_contact_phone ?? 'No phone' }}</small>
                    </div>
                </div>
            </div>

            <div class="card-custom mb-4">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse text-danger"></i>
                    <span class="fw-bold">Clinical Profile & Vitals</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Blood Group</small>
                                <span class="fw-bold fs-6 text-dark">{{ $patient->blood_group ?? 'Unknown' }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Diabetes Status</small>
                                <span class="fw-bold small text-dark">{{ $patient->diabetes_status ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <small class="text-muted d-block">Blood Pressure Reading</small>
                        <span class="fw-semibold text-dark">{{ $patient->blood_pressure_formatted }}</span>
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <small class="text-muted d-block">Known Allergies</small>
                        <span class="text-dark">{{ $patient->allergies ?? 'None reported' }}</span>
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <small class="text-muted d-block">Existing Conditions</small>
                        <span class="text-dark">{{ $patient->existing_conditions ?? 'None' }}</span>
                    </div>

                    <div class="mb-3 pb-2 border-bottom">
                        <small class="text-muted d-block">Current Medications</small>
                        <span class="text-dark">{{ $patient->current_medications ?? 'None' }}</span>
                    </div>

                    <div>
                        <small class="text-muted d-block">Clinical Notes</small>
                        <p class="small text-secondary mb-0">{{ $patient->clinical_notes ?? 'No additional notes recorded.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment History (Right Column) -->
        <div class="col-12 col-lg-8">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        <span class="fw-bold">Appointment History</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $patient->appointments->count() }} visits recorded</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>Code & Date</th>
                                    <th>Doctor & Specialty</th>
                                    <th>Primary Complaint</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Slip</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patient->appointments as $apt)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark d-block">{{ $apt->formatted_date }}</span>
                                            <small class="text-muted">{{ $apt->formatted_time }} • {{ $apt->appointment_code }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('doctors.show', $apt->doctor) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $apt->doctor->name }}
                                            </a>
                                            <small class="text-muted d-block">{{ $apt->doctor->specialty->name }} ({{ $apt->doctor->consultation_room }})</small>
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
                                            <a href="{{ route('appointments.slip', $apt) }}" class="btn btn-sm btn-light border py-1 px-2" target="_blank" title="Print Slip">
                                                <i class="bi bi-printer text-secondary"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No appointments found for this patient.
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
