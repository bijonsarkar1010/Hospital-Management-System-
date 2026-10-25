<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_code',
        'patient_id',
        'doctor_id',
        'requested_date',
        'requested_time',
        'appointment_date',
        'appointment_time',
        'end_time',
        'primary_complaint',
        'status',
        'booking_mode',
        'is_alternative_slot',
        'cancellation_reason',
        'rescheduled_from_id',
        'consultation_notes',
        'created_by',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'appointment_date' => 'date',
        'is_alternative_slot' => 'boolean',
    ];

    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_CHECKED_IN = 'Checked In';
    public const STATUS_IN_CONSULTATION = 'In Consultation';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_NO_SHOW = 'No Show';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_CHECKED_IN,
            self::STATUS_IN_CONSULTATION,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_NO_SHOW,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'rescheduled_from_id');
    }

    public function prediction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AppointmentPrediction::class);
    }


    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SCHEDULED => 'badge bg-primary text-white',
            self::STATUS_CHECKED_IN => 'badge bg-info text-dark',
            self::STATUS_IN_CONSULTATION => 'badge bg-warning text-dark',
            self::STATUS_COMPLETED => 'badge bg-success text-white',
            self::STATUS_CANCELLED => 'badge bg-danger text-white',
            self::STATUS_NO_SHOW => 'badge bg-secondary text-white',
            default => 'badge bg-light text-dark',
        };
    }

    public function getFormattedTimeAttribute(): string
    {
        return Carbon::parse($this->appointment_time)->format('h:i A');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('h:i A');
    }

    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->appointment_date)->format('M d, Y');
    }
}
