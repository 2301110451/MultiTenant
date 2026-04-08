<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $connection = 'tenant';

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'description',
        'quantity_total',
        'quantity_available',
        'condition_status',
        'penalty_per_day',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'penalty_per_day' => 'decimal:2',
        ];
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'equipment_reservation')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }
}
