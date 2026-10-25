<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'primary_complaint',
        'predicted_specialty_id',
        'confirmed_specialty_id',
        'is_override',
        'override_reason',
        'confidence',
        'explanation',
        'is_urgent',
        'matched_rule_id',
    ];

    protected $casts = [
        'is_override' => 'boolean',
        'is_urgent' => 'boolean',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function predictedSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'predicted_specialty_id');
    }

    public function confirmedSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'confirmed_specialty_id');
    }

    public function matchedRule(): BelongsTo
    {
        return $this->belongsTo(SymptomRule::class, 'matched_rule_id');
    }
}
