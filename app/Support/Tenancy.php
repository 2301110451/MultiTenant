<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Tenant;

class Tenancy
{
    public static function isCentralHost(?string $host): bool
    {
        if ($host === null) {
            return true;
        }

        $host = strtolower($host);

        return in_array($host, self::centralHosts(), true);
    }

    /**
     * @return list<string>
     */
    public static function centralHosts(): array
    {
        return array_map('strtolower', config('tenancy.central_hosts', []));
    }

    public static function currentTenant(): ?Tenant
    {
        return app()->bound('currentTenant') ? app('currentTenant') : null;
    }

    public static function tenantPlan(): ?Plan
    {
        $tenant = self::currentTenant();
        if ($tenant === null) {
            return null;
        }

        return $tenant->subscription?->plan ?? $tenant->plan;
    }

    /**
     * Build an absolute URL to a tenant portal by domain (for links from central admin).
     */
    public static function tenantPortalUrl(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $base = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($base, PHP_URL_PORT);

        if ($port && ! in_array((int) $port, [80, 443], true)) {
            return "{$scheme}://{$domain}:{$port}";
        }

        return "{$scheme}://{$domain}";
    }
}
