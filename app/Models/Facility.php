<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'rules',
        'operating_hours',
        'hourly_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'operating_hours' => 'array',
            'is_active' => 'boolean',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
