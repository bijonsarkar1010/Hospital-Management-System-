<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Specialties
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Doctors
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_code')->unique()->index();
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('qualifications');
            $table->unsignedInteger('experience_years')->default(0);
            $table->string('consultation_room');
            $table->unsignedInteger('consultation_duration')->default(30); // in minutes
            $table->boolean('is_active')->default(true)->index();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        // Doctor Schedules (weekly recurring)
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 1=Monday ... 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['doctor_id', 'day_of_week']);
        });

        // Doctor Breaks
        Schema::create('doctor_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->nullable(); // null means applies to all working days
            $table->time('start_time');
            $table->time('end_time');
            $table->string('label')->default('Lunch Break');
            $table->timestamps();

            $table->index(['doctor_id', 'day_of_week']);
        });

        // Doctor Unavailability (Leave, Holidays, Blocked periods)
        Schema::create('doctor_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable(); // null if full day
            $table->time('end_time')->nullable();
            $table->string('type')->default('leave'); // leave, holiday, emergency, conference
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'start_date', 'end_date']);
        });

        // Patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique()->index();
            $table->string('name');
            $table->date('date_of_birth')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('sex'); // Male, Female, Other
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('blood_group')->nullable(); // A+, A-, B+, B-, AB+, AB-, O+, O-, Unknown
            $table->string('diabetes_status')->default('Unknown'); // Diabetic, Non-Diabetic, Unknown
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->text('allergies')->nullable();
            $table->text('existing_conditions')->nullable();
            $table->text('current_medications')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->timestamps();
        });

        // Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code')->unique()->index();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->date('requested_date');
            $table->time('requested_time');
            $table->date('appointment_date')->index();
            $table->time('appointment_time');
            $table->time('end_time');
            $table->text('primary_complaint');
            $table->string('status')->default('Scheduled')->index(); // Scheduled, Checked In, In Consultation, Completed, Cancelled, No Show
            $table->string('booking_mode')->default('auto'); // auto, specialty, doctor
            $table->boolean('is_alternative_slot')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->unsignedBigInteger('rescheduled_from_id')->nullable();
            $table->text('consultation_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date', 'status']);
            $table->index(['patient_id', 'appointment_date']);
        });

        // Clinic Settings
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctor_unavailabilities');
        Schema::dropIfExists('doctor_breaks');
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('specialties');
    }
};
