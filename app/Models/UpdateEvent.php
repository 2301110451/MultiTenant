<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateEvent extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'source',
        'delivery_id',
        'event_type',
        'ref',
        'commit_sha',
        'tag',
        'payload',
        'normalized',
        'status',
        'received_at',
        'processed_at',
        'processing_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'normalized' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
