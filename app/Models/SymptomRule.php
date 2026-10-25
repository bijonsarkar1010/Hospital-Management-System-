<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SymptomRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'symptom_entity',
        'intent',
        'specialty_id',
        'keywords',
        'synonyms',
        'priority',
        'is_urgent',
        'urgent_instructions',
        'is_active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'synonyms' => 'array',
        'priority' => 'integer',
        'is_urgent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Target clinical specialty for this symptom rule.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Active rules scope.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Urgent rules scope.
     */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Order by highest priority first.
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')->orderBy('id', 'asc');
    }
}
