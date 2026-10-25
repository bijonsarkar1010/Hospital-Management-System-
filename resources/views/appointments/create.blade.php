@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Book an Appointment</h4>
            <p class="text-muted small mb-0">Select an existing registered patient and set scheduling preferences with real-time clinical triage assistance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-person-plus me-1"></i> New Patient Registration
            </a>
            <a href="{{ route('appointments.index') }}" class="btn btn-sm btn-light border">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-8">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-plus text-primary fs-5"></i>
                        <span class="fw-bold">Appointment Scheduling Engine</span>
                    </div>
                    <span class="badge bg-light text-muted border">Triage Enabled</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('appointments.allocate') }}">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Select Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">-- Choose Registered Patient --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ (old('patient_id') == $p->id || (isset($selectedPatient) && $selectedPatient->id == $p->id)) ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->patient_code }}) - Phone: {{ $p->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Primary Complaint with Real-Time Prediction -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                <span>Primary Complaint / Symptoms <span class="text-danger">*</span></span>
                                <span class="small text-muted" id="predictionStatus" style="font-size: 0.75rem;">Type symptoms for live specialty suggestion</span>
                            </label>
                            <textarea name="primary_complaint" id="primaryComplaintInput" class="form-control @error('primary_complaint') is-invalid @enderror" rows="2" placeholder="e.g. Skin rash and severe itching on forearm, or Palpitations and rapid pulse" required>{{ old('primary_complaint') }}</textarea>
                            @error('primary_complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <!-- Real-Time Prediction Feedback Card -->
                            <div id="livePredictionCard" class="mt-2 p-3 rounded-3 border bg-light d-none">
                                <!-- Urgent Emergency Banner -->
                                <div id="urgentAlertBanner" class="alert alert-danger py-2 px-3 small d-none mb-2">
                                    <div class="fw-bold d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                                        <span>CRITICAL EMERGENCY SAFEGUARD TRIGGERED</span>
                                    </div>
                                    <div id="urgentInstructionsText" class="mt-1"></div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted text-uppercase fw-semibold" style="font-size: 0.75rem;">Predicted Specialty:</span>
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

                        <!-- Preferred Date & Time -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Requested Date <span class="text-danger">*</span></label>
                                <input type="date" name="requested_date" class="form-control @error('requested_date') is-invalid @enderror" value="{{ old('requested_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required>
                                @error('requested_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Requested Time <span class="text-danger">*</span></label>
                                <input type="time" name="requested_time" class="form-control @error('requested_time') is-invalid @enderror" value="{{ old('requested_time', now()->addMinutes(30)->format('H:00')) }}" required>
                                @error('requested_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Doctor Allocation Mode -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-block">Doctor Assignment Option <span class="text-danger">*</span></label>
                            
                            <div class="border rounded p-2 mb-2 bg-light-subtle">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="booking_mode" id="modeAuto" value="auto" {{ old('booking_mode', 'auto') === 'auto' ? 'checked' : '' }} onchange="toggleBookingMode()">
                                    <label class="form-check-label fw-bold small text-dark" for="modeAuto">
                                        Option A: Automatic Specialty-Constrained Assignment <span class="badge bg-success-subtle text-success">Recommended</span>
                                    </label>
                                    <div class="small text-muted" style="font-size: 0.78rem;">
                                        Allocates suitable doctor within the diagnosed specialty with lowest workload and earliest slot.
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
                                        Manually choose or override target medical department.
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
                                        Check only this doctor's schedule; offer next slot if occupied.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specialty Select (Mode B) -->
                        <div class="mb-3 d-none" id="specialtyContainer">
                            <label class="form-label small fw-semibold">Select Medical Specialty</label>
                            <select name="preferred_specialty_id" id="preferredSpecialtySelect" class="form-select form-select-sm">
                                <option value="">-- Choose Specialty --</option>
                                @foreach($specialties as $sp)
                                    <option value="{{ $sp->id }}" {{ old('preferred_specialty_id') == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Doctor Select (Mode C) -->
                        <div class="mb-3 d-none" id="doctorContainer">
                            <label class="form-label small fw-semibold">Select Specific Doctor</label>
                            <select name="preferred_doctor_id" id="preferredDoctorSelect" class="form-select form-select-sm">
                                <option value="">-- Choose Doctor --</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" {{ old('preferred_doctor_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }} ({{ $d->specialty->name }}) - {{ $d->consultation_room }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Specialty Override Reason (Optional) -->
                        <div class="mb-3" id="overrideReasonGroup">
                            <label class="form-label small fw-semibold text-muted">Override Rationale (Optional)</label>
                            <input type="text" name="override_reason" class="form-control form-control-sm" placeholder="Provide note if selecting a doctor or specialty differing from the predicted department..." value="{{ old('override_reason') }}">
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-cms-primary w-100 py-2 shadow-sm fw-bold">
                                Find Slot & Review Allocation <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleBookingMode() {
        const checkedMode = document.querySelector('input[name="booking_mode"]:checked');
        const mode = checkedMode ? checkedMode.value : 'auto';
        const specialtyContainer = document.getElementById('specialtyContainer');
        const doctorContainer = document.getElementById('doctorContainer');
        const specialtySelect = document.getElementById('preferredSpecialtySelect');
        const doctorSelect = document.getElementById('preferredDoctorSelect');

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
        const specialtySelect = document.getElementById('preferredSpecialtySelect');

        let lastPredictedId = null;
        let debounceTimer = null;

        function runPrediction() {
            const text = complaintInput.value.trim();
            if (text.length < 3) {
                liveCard.classList.add('d-none');
                statusSpan.textContent = 'Type symptoms for live specialty suggestion';
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
                        urgentInstructions.textContent = data.urgent_instructions || 'Emergency protocol advised.';
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

        complaintInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(runPrediction, 350);
        });

        // Trigger on load if initial text present
        if (complaintInput.value.trim().length >= 3) {
            runPrediction();
        }

        // Accept Specialty Quick Action
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
