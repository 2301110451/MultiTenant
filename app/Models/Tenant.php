<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'database',
        'status',
        'plan_id',
        'usage_reservations_month',
    ];

    protected function casts(): array
    {
        return [
            'usage_reservations_month' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionIntents(): HasMany
    {
        return $this->hasMany(TenantSubscriptionIntent::class);
    }

    public function configureTenantConnection(): void
    {
        config([
            'database.connections.tenant.database' => $this->database,
        ]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function runTenantMigrations(): void
    {
        $this->configureTenantConnection();
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }
}
