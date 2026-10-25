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
        Schema::create('appointment_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->text('primary_complaint');
            $table->foreignId('predicted_specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
            $table->foreignId('confirmed_specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->string('confidence', 20)->default('Medium'); // High, Medium, Low, Unknown
            $table->text('explanation')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->foreignId('matched_rule_id')->nullable()->constrained('symptom_rules')->nullOnDelete();
            $table->timestamps();

            $table->index(['appointment_id', 'is_urgent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_predictions');
    }
};
