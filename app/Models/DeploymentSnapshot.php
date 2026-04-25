<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentSnapshot extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'version',
        'artifact_digest',
        'artifact_uri',
        'code_reference',
        'lockfile_hash',
        'config_hash',
        'metadata',
        'created_by',
        'created_at_snapshot',
        'is_stable',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at_snapshot' => 'datetime',
            'is_stable' => 'boolean',
        ];
    }
}
