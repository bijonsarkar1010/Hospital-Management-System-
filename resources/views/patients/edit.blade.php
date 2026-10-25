@extends('layouts.app')

@section('title', 'Edit Patient - ' . $patient->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Edit Patient Information</h4>
            <p class="text-muted small mb-0">Update contact details, vitals, or medical history for patient #{{ $patient->patient_code }}.</p>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Cancel & Return
        </a>
    </div>

    <form method="POST" action="{{ route('patients.update', $patient) }}">
        @csrf
        @method('PUT')

        <div class="card-custom mb-4">
            <div class="card-header-clean d-flex align-items-center gap-2">
                <i class="bi bi-person text-primary"></i>
                <span class="fw-bold">Patient Demographics & Medical Profile</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $patient->name) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone) }}" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->toDateString() : '') }}">
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Age (Years) <span class="text-danger">*</span></label>
                        <input type="number" name="age" class="form-control" value="{{ old('age', $patient->effective_age) }}" required>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Sex <span class="text-danger">*</span></label>
                        <select name="sex" class="form-select" required>
                            <option value="Male" {{ old('sex', $patient->sex) == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex', $patient->sex) == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('sex', $patient->sex) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Residential Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $patient->address) }}" required>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-semibold">Diabetes Status</label>
                        <select name="diabetes_status" class="form-select">
                            @foreach(['Unknown', 'Non-Diabetic', 'Diabetic'] as $ds)
                                <option value="{{ $ds }}" {{ old('diabetes_status', $patient->diabetes_status) == $ds ? 'selected' : '' }}>{{ $ds }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold">Blood Pressure (mmHg)</label>
                        <div class="input-group">
                            <input type="number" name="blood_pressure_systolic" class="form-control" placeholder="Sys" value="{{ old('blood_pressure_systolic', $patient->blood_pressure_systolic) }}">
                            <span class="input-group-text">/</span>
                            <input type="number" name="blood_pressure_diastolic" class="form-control" placeholder="Dia" value="{{ old('blood_pressure_diastolic', $patient->blood_pressure_diastolic) }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Allergies</label>
                        <input type="text" name="allergies" class="form-control" value="{{ old('allergies', $patient->allergies) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Existing Conditions</label>
                        <input type="text" name="existing_conditions" class="form-control" value="{{ old('existing_conditions', $patient->existing_conditions) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Current Medications</label>
                        <input type="text" name="current_medications" class="form-control" value="{{ old('current_medications', $patient->current_medications) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Emergency Contact (Name & Phone)</label>
                        <div class="input-group">
                            <input type="text" name="emergency_contact_name" class="form-control" placeholder="Name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}">
                            <input type="text" name="emergency_contact_phone" class="form-control" placeholder="Phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Clinical Notes</label>
                        <textarea name="clinical_notes" class="form-control" rows="2">{{ old('clinical_notes', $patient->clinical_notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-cms-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
