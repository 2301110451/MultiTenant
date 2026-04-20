<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateAnnouncement extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'title',
        'message',
        'audience',
        'targeted_tenant_ids',
        'is_active',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'targeted_tenant_ids' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
