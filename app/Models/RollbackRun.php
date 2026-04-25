<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RollbackRun extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'deployment_run_id',
        'to_snapshot_id',
        'trigger_type',
        'status',
        'triggered_by',
        'reason',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function deploymentRun(): BelongsTo
    {
        return $this->belongsTo(DeploymentRun::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(DeploymentSnapshot::class, 'to_snapshot_id');
    }
}
