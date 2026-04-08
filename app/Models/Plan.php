<?php

namespace App\Models;

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
        $f = $this->features ?? [];
        if ($f === []) {
            return false;
        }

        // Seeder / admin JSON: associative array of booleans
        if (! array_is_list($f)) {
            return (bool) ($f[$feature] ?? false);
        }

        // Feature toggles stored as array of strings (e.g. reports, qr, payments)
        if (in_array($feature, $f, true)) {
            return true;
        }

        if ($feature === 'qr_checkin' && in_array('qr', $f, true)) {
            return true;
        }

        if ($feature === 'qr' && in_array('qr_checkin', $f, true)) {
            return true;
        }

        return false;
    }
}
