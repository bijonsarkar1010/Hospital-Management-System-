@extends('layouts.app')

@section('title', 'Edit Doctor - ' . $doctor->name)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Edit Doctor Profile</h4>
            <p class="text-muted small mb-0">Update medical information for {{ $doctor->name }} ({{ $doctor->doctor_code }}).</p>
        </div>
        <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Cancel
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-5"></i>
                    <span class="fw-bold">Doctor Profile & Configuration</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('doctors.update', $doctor) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold">Doctor Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->name) }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Specialty <span class="text-danger">*</span></label>
                                <select name="specialty_id" class="form-select" required>
                                    @foreach($specialties as $sp)
                                        <option value="{{ $sp->id }}" {{ old('specialty_id', $doctor->specialty_id) == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $doctor->email) }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $doctor->phone) }}" required>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold">Qualifications & Degrees <span class="text-danger">*</span></label>
                                <input type="text" name="qualifications" class="form-control" value="{{ old('qualifications', $doctor->qualifications) }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Experience (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="experience_years" class="form-control" value="{{ old('experience_years', $doctor->experience_years) }}" min="0" max="60" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Consultation Room <span class="text-danger">*</span></label>
                                <input type="text" name="consultation_room" class="form-control" value="{{ old('consultation_room', $doctor->consultation_room) }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Consultation Duration <span class="text-danger">*</span></label>
                                <select name="consultation_duration" class="form-select" required>
                                    <option value="15" {{ old('consultation_duration', $doctor->consultation_duration) == 15 ? 'selected' : '' }}>15 minutes</option>
                                    <option value="20" {{ old('consultation_duration', $doctor->consultation_duration) == 20 ? 'selected' : '' }}>20 minutes</option>
                                    <option value="30" {{ old('consultation_duration', $doctor->consultation_duration) == 30 ? 'selected' : '' }}>30 minutes (Standard)</option>
                                    <option value="45" {{ old('consultation_duration', $doctor->consultation_duration) == 45 ? 'selected' : '' }}>45 minutes (Extended)</option>
                                    <option value="60" {{ old('consultation_duration', $doctor->consultation_duration) == 60 ? 'selected' : '' }}>60 minutes</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Active Status <span class="text-danger">*</span></label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" {{ old('is_active', $doctor->is_active) ? 'selected' : '' }}>Active (Accepts Appointments)</option>
                                    <option value="0" {{ !old('is_active', $doctor->is_active) ? 'selected' : '' }}>Inactive (Off schedule)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Professional Bio / Notes</label>
                                <textarea name="bio" class="form-control" rows="2">{{ old('bio', $doctor->bio) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-cms-primary px-4 shadow-sm fw-bold">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
