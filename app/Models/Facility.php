<?php

namespace App\Models;

use App\Enums\FacilityKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'kind',
        'image_path',
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
            'kind' => FacilityKind::class,
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
