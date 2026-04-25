<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentRun extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'deployment_candidate_id',
        'snapshot_id',
        'status',
        'environment',
        'strategy',
        'requested_by',
        'approved_by',
        'validation_workflow_run_id',
        'validation_report',
        'health_metrics',
        'validated_at',
        'deployed_at',
        'rolled_back_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'validation_report' => 'array',
            'health_metrics' => 'array',
            'validated_at' => 'datetime',
            'deployed_at' => 'datetime',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(DeploymentCandidate::class, 'deployment_candidate_id');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(DeploymentSnapshot::class, 'snapshot_id');
    }
}
