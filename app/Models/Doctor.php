<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_code',
        'specialty_id',
        'name',
        'email',
        'phone',
        'qualifications',
        'experience_years',
        'consultation_room',
        'consultation_duration',
        'is_active',
        'bio',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'experience_years' => 'integer',
        'consultation_duration' => 'integer',
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(DoctorBreak::class);
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(DoctorUnavailability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
