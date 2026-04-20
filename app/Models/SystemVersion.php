<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemVersion extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'version',
        'release_type',
        'notes',
        'migration_batch',
        'released_at',
        'released_by',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
        ];
    }
}
