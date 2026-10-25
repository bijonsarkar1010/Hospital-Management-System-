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
        Schema::create('patient_deletion_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->unsignedBigInteger('patient_id_recorded')->nullable()->index();
            $table->string('patient_code')->index();
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->text('reason');
            $table->string('ip_address', 45)->nullable();
            $table->string('outcome')->default('success'); // success, rejected_active_appointments, failed
            $table->text('notes')->nullable();
            $table->json('archived_patient_summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_deletion_audits');
    }
};
