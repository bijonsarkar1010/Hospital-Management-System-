<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code',
        'name',
        'date_of_birth',
        'age',
        'sex',
        'phone',
        'email',
        'address',
        'blood_group',
        'diabetes_status',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'allergies',
        'existing_conditions',
        'current_medications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'clinical_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
        'blood_pressure_systolic' => 'integer',
        'blood_pressure_diastolic' => 'integer',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc');
    }

    public function getEffectiveAgeAttribute(): int
    {
        if ($this->date_of_birth) {
            return Carbon::parse($this->date_of_birth)->age;
        }
        return (int) ($this->age ?? 0);
    }

    public function getBloodPressureFormattedAttribute(): string
    {
        if ($this->blood_pressure_systolic && $this->blood_pressure_diastolic) {
            return "{$this->blood_pressure_systolic}/{$this->blood_pressure_diastolic} mmHg";
        }
        return 'Not recorded';
    }

    public function lastAppointment()
    {
        return $this->hasOne(Appointment::class)
            ->where('appointment_date', '<=', now()->toDateString())
            ->where('status', '!=', 'Cancelled')
            ->latest('appointment_date')
            ->latest('appointment_time');
    }

    public function upcomingAppointment()
    {
        return $this->hasOne(Appointment::class)
            ->where('appointment_date', '>=', now()->toDateString())
            ->whereIn('status', ['Scheduled', 'Checked In'])
            ->oldest('appointment_date')
            ->oldest('appointment_time');
    }
}
