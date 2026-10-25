@extends('layouts.app')

@section('title', 'Clinic Settings')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Clinic Configuration & Settings</h4>
            <p class="text-muted small mb-0">Manage clinic operational parameters, appointment horizons, and facility details.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-8">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-gear text-primary fs-5"></i>
                    <span class="fw-bold">Clinic Facility & Scheduling Preferences</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Facility Identity</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold">Clinic Name <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" class="form-control" value="{{ old('clinic_name', $settings['clinic_name']) }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_phone" class="form-control" value="{{ old('clinic_phone', $settings['clinic_phone']) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Tagline / Subtitle</label>
                                <input type="text" name="clinic_tagline" class="form-control" value="{{ old('clinic_tagline', $settings['clinic_tagline']) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Reception Email <span class="text-danger">*</span></label>
                                <input type="email" name="clinic_email" class="form-control" value="{{ old('clinic_email', $settings['clinic_email']) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Physical Address <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_address" class="form-control" value="{{ old('clinic_address', $settings['clinic_address']) }}" required>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Operating Hours & Booking Engine Rules</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Clinic Opens <span class="text-danger">*</span></label>
                                <input type="time" name="clinic_open_time" class="form-control" value="{{ old('clinic_open_time', $settings['clinic_open_time']) }}" required>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Clinic Closes <span class="text-danger">*</span></label>
                                <input type="time" name="clinic_close_time" class="form-control" value="{{ old('clinic_close_time', $settings['clinic_close_time']) }}" required>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Booking Horizon (Days) <span class="text-danger">*</span></label>
                                <input type="number" name="booking_horizon_days" class="form-control" value="{{ old('booking_horizon_days', $settings['booking_horizon_days']) }}" min="1" max="90" required>
                                <div class="form-text small" style="font-size: 0.72rem;">Days ahead engine searches.</div>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold">Default Duration (Min) <span class="text-danger">*</span></label>
                                <input type="number" name="default_consultation_duration" class="form-control" value="{{ old('default_consultation_duration', $settings['default_consultation_duration']) }}" min="5" max="120" required>
                                <div class="form-text small" style="font-size: 0.72rem;">Fallback duration.</div>
                            </div>
                        </div>

                        <div class="pt-3 border-top text-end">
                            <button type="submit" class="btn btn-cms-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Save Clinic Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Administrative Modules -->
            <div class="row g-3 mt-3">
                <!-- Symptom Rules Engine Card -->
                <div class="col-12 col-md-6">
                    <div class="card-custom h-100 p-3 d-flex flex-column justify-content-between border-primary-subtle">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="bi bi-diagram-3 fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">Symptom Rules Engine</h6>
                            </div>
                            <p class="small text-muted mb-3">
                                Configure database mapping between symptoms, intents, and medical specialties. Includes live test simulator and red-flag rules.
                            </p>
                        </div>
                        <a href="{{ route('settings.symptom_rules.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                            Manage Symptom Rules <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Restricted Patient Deletion Card -->
                <div class="col-12 col-md-6">
                    <div class="card-custom h-100 p-3 d-flex flex-column justify-content-between border-danger-subtle bg-danger bg-opacity-10">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-danger text-white p-2 rounded-3">
                                        <i class="bi bi-shield-slash fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-danger">Restricted Actions</h6>
                                </div>
                                <span class="badge bg-danger">Authorized Admin</span>
                            </div>
                            <p class="small text-muted mb-3">
                                Permanent patient record purging. Strictly audited, checks active bookings, requires confirmation, and executes in safe transactions.
                            </p>
                        </div>
                        <a href="{{ route('settings.restricted.patient_deletion') }}" class="btn btn-sm btn-danger fw-semibold">
                            Permanent Patient Deletion <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
