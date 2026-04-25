<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantActivityAuditLog extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'actor_type',
        'actor_user_id',
        'actor_name',
        'actor_email',
        'module',
        'action',
        'event_key',
        'status',
        'target_type',
        'target_id',
        'target_label',
        'before_values',
        'after_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'actor_user_id' => 'integer',
            'target_id' => 'integer',
            'before_values' => 'array',
            'after_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
