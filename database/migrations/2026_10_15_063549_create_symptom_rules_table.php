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
        Schema::create('symptom_rules', function (Blueprint $table) {
            $table->id();
            $table->string('symptom_entity'); // e.g., "Skin rash", "Palpitations"
            $table->string('intent'); // e.g., "Skin complaint", "Heart-related complaint"
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->json('keywords')->nullable(); // list of keyword strings and phrases
            $table->json('synonyms')->nullable(); // additional colloquial or medical synonyms
            $table->integer('priority')->default(10); // Higher weight = higher matching precedence
            $table->boolean('is_urgent')->default(false); // Flag for emergency red-flag conditions
            $table->text('urgent_instructions')->nullable(); // Clinical guidance when urgent
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptom_rules');
    }
};
