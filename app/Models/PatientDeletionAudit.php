<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDeletionAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_email',
        'patient_id_recorded',
        'patient_code',
        'patient_name',
        'patient_phone',
        'reason',
        'ip_address',
        'outcome',
        'notes',
        'archived_patient_summary',
    ];

    protected $casts = [
        'archived_patient_summary' => 'array',
    ];

    /**
     * The admin user who initiated or requested the deletion.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
