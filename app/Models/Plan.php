<?php

namespace App\Models;

use App\Support\Pricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'slug',
        'monthly_reservation_limit',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'monthly_reservation_limit' => 'integer',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function allows(string $feature): bool
    {
        return Pricing::allows($feature, $this);
    }
}
