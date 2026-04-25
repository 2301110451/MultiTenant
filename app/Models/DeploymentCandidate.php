<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentCandidate extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'update_event_id',
        'risk_level',
        'risk_score',
        'change_summary',
        'affected_modules',
        'blast_radius',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'decision_note',
    ];

    protected function casts(): array
    {
        return [
            'affected_modules' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function updateEvent(): BelongsTo
    {
        return $this->belongsTo(UpdateEvent::class);
    }
}
