<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalUpdateAuditLog extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'actor_user_id',
        'action',
        'status',
        'message',
        'scope',
        'update_type',
        'version',
        'github_release_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'actor_user_id' => 'integer',
            'github_release_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(CentralUser::class, 'actor_user_id');
    }
}
