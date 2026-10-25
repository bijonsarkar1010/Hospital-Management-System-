@extends('layouts.app')

@section('title', 'Add New Doctor')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Add New Doctor</h4>
            <p class="text-muted small mb-0">Register a new medical practitioner and assign consultation parameters.</p>
        </div>
        <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Doctors
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card-custom">
                <div class="card-header-clean d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus text-primary fs-5"></i>
                    <span class="fw-bold">Doctor Profile Information</span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('doctors.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold">Doctor Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Dr. Arthur Conan" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Specialty <span class="text-danger">*</span></label>
                                <select name="specialty_id" class="form-select @error('specialty_id') is-invalid @enderror" required>
                                    <option value="">-- Specialty --</option>
                                    @foreach($specialties as $sp)
                                        <option value="{{ $sp->id }}" {{ old('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                                    @endforeach
                                </select>
                                @error('specialty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="doctor@carepoint.com" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+1 (555) 401-1031" required>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold">Qualifications & Degrees <span class="text-danger">*</span></label>
                                <input type="text" name="qualifications" class="form-control @error('qualifications') is-invalid @enderror" value="{{ old('qualifications') }}" placeholder="e.g. MD, FACP, Board Certified Internal Medicine" required>
                                @error('qualifications') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Experience (Years) <span class="text-danger">*</span></label>
                                <input type="number" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror" value="{{ old('experience_years', 5) }}" min="0" max="60" required>
                                @error('experience_years') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Consultation Room <span class="text-danger">*</span></label>
                                <input type="text" name="consultation_room" class="form-control @error('consultation_room') is-invalid @enderror" value="{{ old('consultation_room', 'Room 101') }}" placeholder="e.g. Consultation Suite 105" required>
                                @error('consultation_room') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold">Consultation Duration <span class="text-danger">*</span></label>
                                <select name="consultation_duration" class="form-select @error('consultation_duration') is-invalid @enderror" required>
                                    <option value="15" {{ old('consultation_duration') == 15 ? 'selected' : '' }}>15 minutes</option>
                                    <option value="20" {{ old('consultation_duration') == 20 ? 'selected' : '' }}>20 minutes</option>
                                    <option value="30" {{ old('consultation_duration', 30) == 30 ? 'selected' : '' }}>30 minutes (Standard)</option>
                                    <option value="45" {{ old('consultation_duration') == 45 ? 'selected' : '' }}>45 minutes (Extended)</option>
                                    <option value="60" {{ old('consultation_duration') == 60 ? 'selected' : '' }}>60 minutes</option>
                                </select>
                                @error('consultation_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Professional Bio / Notes</label>
                                <textarea name="bio" class="form-control" rows="2" placeholder="Brief summary of professional background...">{{ old('bio') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-cms-primary px-4 shadow-sm fw-bold">
                                Save Doctor & Configure Schedule <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
