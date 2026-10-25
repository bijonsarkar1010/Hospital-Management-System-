@extends('layouts.app')

@section('title', 'Register Patient & Schedule Appointment')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Patient Registration & Appointment Scheduling</h4>
            <p class="text-muted small mb-0">Enter patient details to automatically assign an eligible doctor and appointment slot.</p>
        </div>
        <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Patient Directory
        </a>
    </div>

    <!-- Duplicate Patient Warning Alert Box (populated by JS) -->
    <div id="duplicateAlert" class="alert alert-warning border-warning d-none mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0"></i>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1 text-dark">Possible Duplicate Patient Record Detected!</h6>
                <p class="small mb-2 text-secondary" id="duplicateMessage">
                    A patient with this mobile number already exists in the system.
                </p>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="duplicateRecordLink" class="btn btn-sm btn-dark" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Existing Patient Record
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('duplicateAlert').classList.add('d-none')">
                        Dismiss & Continue Creating
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('patients.store') }}" id="registrationForm">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Patient Demographic & Medical Info -->
            <div class="col-12 col-lg-7">
                <div class="card-custom mb-4">
                    <div class="card-header-clean d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-primary fs-5"></i>
                        <span class="fw-bold">1. Patient Demographic Information</span>
                        <span class="badge bg-danger-subtle text-danger ms-auto small">* Required</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Full Patient Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Johnathan Doe" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Mobile Phone (with live duplicate checking) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Mobile Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" id="phoneInput" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="e.g. +1 (555) 019-2831" required>
                                </div>
                                <div class="form-text" id="phoneCheckStatus">System will check for duplicate phone numbers.</div>
                                @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Email Address <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="patient@example.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Date of Birth & Age -->
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="dobInput" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}">
                                @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-semibold">Age (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="age" id="ageInput" class="form-control @error('age') is-invalid @enderror" value="{{ old('age') }}" min="0" max="120" placeholder="e.g. 35" required>
                                @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Sex -->
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-semibold">Sex / Gender <span class="text-danger">*</span></label>
                                <select name="sex" class="form-select @error('sex') is-invalid @enderror" required>
                                    <option value="">Select Sex</option>
                                    <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('sex') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('sex') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Residential Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Street address, city, postal code" required>{{ old('address') }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional Clinical & Emergency Information -->
                <div class="card-custom mb-4">
                    <div class="card-header-clean d-flex align-items-center gap-2">
                        <i class="bi bi-file-medical text-primary fs-5"></i>
                        <span class="fw-bold">2. Clinical Background & Vitals</span>
                        <span class="badge bg-secondary-subtle text-secondary ms-auto small">Optional Information</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Blood Group -->
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-semibold">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="Unknown" {{ old('blood_group') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                                    <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                            </div>

                            <!-- Diabetes Status -->
                            <div class="col-6 col-md-4">
                                <label class="form-label small fw-semibold">Diabetes Status</label>
                                <select name="diabetes_status" class="form-select">
                                    <option value="Unknown" {{ old('diabetes_status') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                                    <option value="Non-Diabetic" {{ old('diabetes_status') == 'Non-Diabetic' ? 'selected' : '' }}>Non-Diabetic</option>
                                    <option value="Diabetic" {{ old('diabetes_status') == 'Diabetic' ? 'selected' : '' }}>Diabetic</option>
                                </select>
                            </div>

                            <!-- Blood Pressure (Systolic & Diastolic) -->
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Blood Pressure (mmHg)</label>
                                <div class="input-group">
                                    <input type="number" name="blood_pressure_systolic" class="form-control" placeholder="120" value="{{ old('blood_pressure_systolic') }}" title="Systolic">
                                    <span class="input-group-text">/</span>
                                    <input type="number" name="blood_pressure_diastolic" class="form-control" placeholder="80" value="{{ old('blood_pressure_diastolic') }}" title="Diastolic">
                                </div>
                            </div>

                            <!-- Known Allergies -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Known Drug/Food Allergies</label>
                                <input type="text" name="allergies" class="form-control" placeholder="e.g. Penicillin, Peanuts (or None)" value="{{ old('allergies') }}">
                            </div>

                            <!-- Existing Medical Conditions -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Existing Medical Conditions</label>
                                <input type="text" name="existing_conditions" class="form-control" placeholder="e.g. Asthma, Hypertension" value="{{ old('existing_conditions') }}">
                            </div>

                            <!-- Current Medications -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Current Medications</label>
                                <input type="text" name="current_medications" class="form-control" placeholder="e.g. Inhaler, Aspirin" value="{{ old('current_medications') }}">
                            </div>

                            <!-- Emergency Contact -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Emergency Contact (Name & Phone)</label>
                                <div class="input-group">
                                    <input type="text" name="emergency_contact_name" class="form-control" placeholder="Contact Name" value="{{ old('emergency_contact_name') }}">
                                    <input type="text" name="emergency_contact_phone" class="form-control" placeholder="Phone" value="{{ old('emergency_contact_phone') }}">
                                </div>
                            </div>

                            <!-- Additional Clinical Notes -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Additional Reception / Clinical Notes</label>
                                <textarea name="clinical_notes" class="form-control" rows="2" placeholder="Any special assistance required (e.g. wheelchair assistance, language translator)">{{ old('clinical_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Appointment Request & Scheduling Engine Selection -->
            <div class="col-12 col-lg-5">
                <div class="card-custom sticky-top" style="top: 80px;">
                    <div class="card-header-clean d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-range text-primary fs-5"></i>
                        <span class="fw-bold">3. Appointment Allocation Criteria</span>
                        <span class="badge bg-primary-subtle text-primary ms-auto">Smart Engine</span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Primary Complaint / Symptoms with Live Clinical Triage -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                <span>Primary Complaint / Reason for Visit <span class="text-danger">*</span></span>
                                <span class="small text-muted" id="predictionStatus" style="font-size: 0.75rem;">Type symptoms for live triage prediction</span>
                            </label>
                            <textarea name="primary_complaint" id="primaryComplaintInput" class="form-control @error('primary_complaint') is-invalid @enderror" rows="2" placeholder="e.g. Severe skin rash with itching, or High fever with earache" required>{{ old('primary_complaint') }}</textarea>
                            @error('primary_complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <!-- Real-Time Prediction Feedback Card -->
                            <div id="livePredictionCard" class="mt-2 p-3 rounded-3 border bg-light d-none">
                                <!-- Urgent Emergency Safeguard Banner -->
                                <div id="urgentAlertBanner" class="alert alert-danger py-2 px-3 small d-none mb-2">
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                                        <span>CRITICAL EMERGENCY SAFEGUARD TRIGGERED</span>
                                    </div>
                                    <div id="urgentInstructionsText" class="mt-1"></div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Suggested Specialty:</span>
                                        <span id="predictedSpecialtyBadge" class="badge bg-primary fs-6 px-2 py-1">--</span>
                                        <span id="confidenceBadge" class="badge bg-success">High Confidence</span>
                                    </div>
                                    <button type="button" id="acceptSpecialtyBtn" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.8rem;">
                                        <i class="bi bi-check2-circle me-1"></i> Use This Specialty
                                    </button>
                                </div>
                                <div class="small text-muted" id="predictionExplanation" style="font-size: 0.82rem;">--</div>
                            </div>
                        </div>

                        <!-- Override Rationale (Optional) -->
                        <div class="mb-3" id="overrideReasonGroup">
                            <label class="form-label small fw-semibold text-muted">Override Rationale (Optional)</label>
                            <input type="text" name="override_reason" class="form-control form-control-sm" placeholder="Provide note if assigning to a specialty/doctor different from the predicted department..." value="{{ old('override_reason') }}">
                        </div>

                        <!-- Preferred Date & Time -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" name="requested_date" id="reqDateInput" class="form-control @error('requested_date') is-invalid @enderror" value="{{ old('requested_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                                @error('requested_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Preferred Time <span class="text-danger">*</span></label>
                                <input type="time" name="requested_time" id="reqTimeInput" class="form-control @error('requested_time') is-invalid @enderror" value="{{ old('requested_time', now()->addMinutes(30)->format('H:00')) }}" required>
                                @error('requested_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Doctor Selection Mode (Option A, B, C) -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-block">Doctor Selection Mode <span class="text-danger">*</span></label>
                            
                            <div class="border rounded p-2 mb-2 bg-light-subtle">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="booking_mode" id="modeAuto" value="auto" {{ old('booking_mode', 'auto') === 'auto' ? 'checked' : '' }} onchange="toggleBookingMode()">
                                    <label class="form-check-label fw-bold small text-dark" for="modeAuto">
                                        Option A: Automatic Doctor Assignment <span class="badge bg-success-subtle text-success">Recommended</span>
                                    </label>
                                    <div class="small text-muted" style="font-size: 0.78rem;">
                                        System identifies earliest available suitable doctor with lowest workload today.
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded p-2 mb-2 bg-light-subtle">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="booking_mode" id="modeSpecialty" value="specialty" {{ old('booking_mode') === 'specialty' ? 'checked' : '' }} onchange="toggleBookingMode()">
                                    <label class="form-check-label fw-bold small text-dark" for="modeSpecialty">
                                        Option B: Medical Specialty Selection
                                    </label>
                                    <div class="small text-muted" style="font-size: 0.78rem;">
                                        Assign an available specialist in a selected medical department.
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded p-2 mb-2 bg-light-subtle">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="booking_mode" id="modeDoctor" value="doctor" {{ old('booking_mode') === 'doctor' ? 'checked' : '' }} onchange="toggleBookingMode()">
                                    <label class="form-check-label fw-bold small text-dark" for="modeDoctor">
                                        Option C: Particular Doctor Selection
                                    </label>
                                    <div class="small text-muted" style="font-size: 0.78rem;">
                                        Check only the specific doctor's schedule; find next slot if occupied.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specialty Dropdown (Shown when Mode B is selected) -->
                        <div class="mb-3 d-none" id="specialtyContainer">
                            <label class="form-label small fw-semibold">Select Medical Specialty</label>
                            <select name="preferred_specialty_id" id="specialtySelect" class="form-select form-select-sm">
                                <option value="">-- Choose Specialty --</option>
                                @foreach($specialties as $sp)
                                    <option value="{{ $sp->id }}" {{ old('preferred_specialty_id') == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Doctor Dropdown (Shown when Mode C is selected) -->
                        <div class="mb-3 d-none" id="doctorContainer">
                            <label class="form-label small fw-semibold">Select Specific Doctor</label>
                            <select name="preferred_doctor_id" id="doctorSelect" class="form-select form-select-sm">
                                <option value="">-- Choose Doctor --</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" {{ old('preferred_doctor_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }} ({{ $d->specialty->name }}) - {{ $d->consultation_room }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Information Notice -->
                        <div class="bg-light p-3 rounded border mb-3 small text-muted">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            <strong>Smart Scheduling Engine:</strong> If the exact requested time is occupied, the engine will automatically find the earliest available alternative slot and prompt for receptionist confirmation.
                        </div>

                        <button type="submit" class="btn btn-cms-primary w-100 py-2 shadow-sm fw-bold">
                            Find Doctor & Continue to Allocation <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Toggle Doctor Selection Modes
    function toggleBookingMode() {
        const checkedMode = document.querySelector('input[name="booking_mode"]:checked');
        const mode = checkedMode ? checkedMode.value : 'auto';
        const specialtyContainer = document.getElementById('specialtyContainer');
        const doctorContainer = document.getElementById('doctorContainer');
        const specialtySelect = document.getElementById('specialtySelect');
        const doctorSelect = document.getElementById('doctorSelect');

        if (mode === 'specialty') {
            specialtyContainer.classList.remove('d-none');
            doctorContainer.classList.add('d-none');
            if (specialtySelect) specialtySelect.required = true;
            if (doctorSelect) doctorSelect.required = false;
        } else if (mode === 'doctor') {
            doctorContainer.classList.remove('d-none');
            specialtyContainer.classList.add('d-none');
            if (doctorSelect) doctorSelect.required = true;
            if (specialtySelect) specialtySelect.required = false;
        } else {
            specialtyContainer.classList.add('d-none');
            doctorContainer.classList.add('d-none');
            if (specialtySelect) specialtySelect.required = false;
            if (doctorSelect) doctorSelect.required = false;
        }
    }

    // Auto-calculate Age from DOB
    const dobInput = document.getElementById('dobInput');
    const ageInput = document.getElementById('ageInput');
    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageInput.value = Math.max(0, age);
            }
        });
    }

    // Real-Time Duplicate Phone Check
    let debounceTimer;
    const phoneInput = document.getElementById('phoneInput');
    const duplicateAlert = document.getElementById('duplicateAlert');
    const duplicateMessage = document.getElementById('duplicateMessage');
    const duplicateRecordLink = document.getElementById('duplicateRecordLink');
    const phoneCheckStatus = document.getElementById('phoneCheckStatus');

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const val = this.value.trim();
            if (val.length < 7) {
                duplicateAlert.classList.add('d-none');
                phoneCheckStatus.textContent = 'Enter full mobile number to check duplicates.';
                return;
            }

            phoneCheckStatus.innerHTML = '<span class="text-primary"><i class="bi bi-arrow-repeat spin"></i> Checking phone number...</span>';

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('patients.check_duplicate') }}?phone=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            duplicateAlert.classList.remove('d-none');
                            duplicateMessage.innerHTML = `Found existing patient: <strong>${data.patient.name}</strong> (${data.patient.patient_code}, ${data.patient.age} yrs, ${data.patient.sex}) with phone <strong>${data.patient.phone}</strong>.`;
                            duplicateRecordLink.href = data.patient.url;
                            phoneCheckStatus.innerHTML = '<span class="text-warning fw-semibold"><i class="bi bi-exclamation-triangle"></i> Possible duplicate detected above.</span>';
                        } else {
                            duplicateAlert.classList.add('d-none');
                            phoneCheckStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Mobile number is unique.</span>';
                        }
                    })
                    .catch(() => {
                        phoneCheckStatus.textContent = 'Duplicate check unavailable.';
                    });
            }, 400);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleBookingMode();

        // Real-Time Symptom Prediction
        const complaintInput = document.getElementById('primaryComplaintInput');
        const liveCard = document.getElementById('livePredictionCard');
        const statusSpan = document.getElementById('predictionStatus');
        const specialtyBadge = document.getElementById('predictedSpecialtyBadge');
        const confBadge = document.getElementById('confidenceBadge');
        const explanationText = document.getElementById('predictionExplanation');
        const acceptBtn = document.getElementById('acceptSpecialtyBtn');
        const urgentBanner = document.getElementById('urgentAlertBanner');
        const urgentInstructions = document.getElementById('urgentInstructionsText');
        const specialtySelect = document.getElementById('specialtySelect');

        let lastPredictedId = null;
        let predictionTimer = null;

        function runPrediction() {
            if (!complaintInput) return;
            const text = complaintInput.value.trim();
            if (text.length < 3) {
                liveCard.classList.add('d-none');
                statusSpan.textContent = 'Type symptoms for live triage prediction';
                return;
            }

            statusSpan.textContent = 'Analyzing symptoms...';

            fetch(`{{ route('api.symptoms.predict') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ complaint: text })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    lastPredictedId = data.suggested_specialty_id;
                    specialtyBadge.textContent = data.suggested_specialty_name;

                    confBadge.textContent = `${data.confidence} Confidence`;
                    confBadge.className = `badge bg-${data.confidence_badge || 'secondary'}`;

                    explanationText.textContent = data.explanation;

                    if (data.is_urgent) {
                        urgentInstructions.textContent = data.urgent_instructions || 'Emergency triage protocol advised.';
                        urgentBanner.classList.remove('d-none');
                    } else {
                        urgentBanner.classList.add('d-none');
                    }

                    liveCard.classList.remove('d-none');
                    statusSpan.textContent = `Diagnosis ready (${data.confidence})`;
                }
            })
            .catch(err => {
                console.error('Prediction error:', err);
                statusSpan.textContent = '';
            });
        }

        if (complaintInput) {
            complaintInput.addEventListener('input', () => {
                clearTimeout(predictionTimer);
                predictionTimer = setTimeout(runPrediction, 350);
            });

            if (complaintInput.value.trim().length >= 3) {
                runPrediction();
            }
        }

        // Quick Apply Action
        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => {
                if (lastPredictedId) {
                    const modeSpecialtyRadio = document.getElementById('modeSpecialty');
                    modeSpecialtyRadio.checked = true;
                    toggleBookingMode();

                    specialtySelect.value = lastPredictedId;
                    acceptBtn.innerHTML = '<i class="bi bi-check-all"></i> Applied!';
                    setTimeout(() => {
                        acceptBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Use This Specialty';
                    }, 1500);
                }
            });
        }
    });
</script>
@endpush
