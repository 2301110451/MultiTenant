<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateAnnouncement extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'title',
        'version',
        'update_type',
        'source',
        'github_release_id',
        'github_tag_name',
        'message',
        'audience',
        'targeted_tenant_ids',
        'is_active',
        'published_at',
        'synced_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'github_release_id' => 'integer',
            'targeted_tenant_ids' => 'array',
            'published_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }
}
