<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Release extends Model
{
    protected $connection = 'mysql';

    public const UPDATED_AT = null;

    protected $fillable = [
        'title',
        'version',
        'suggested_version',
        'release_type',
        'notes',
        'status',
        'changes_detected',
        'files_affected',
        'risk_level',
        'source_commit_sha',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'changes_detected' => 'array',
            'files_affected' => 'array',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReleaseLog::class);
    }
}
