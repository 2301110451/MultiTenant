<?php

namespace App\Services;

use App\Enums\TenantRole;
use App\Models\Domain;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    public function createDatabase(string $databaseName): void
    {
        if (! $this->isValidDatabaseName($databaseName)) {
            throw new \InvalidArgumentException('Invalid database name.');
        }

        $charset = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        DB::connection('mysql')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}"
        );
    }

    /**
     * Permanently remove a tenant's MySQL database (used when deleting a tenant).
     */
    public function dropDatabase(string $databaseName): void
    {
        if (! $this->isValidDatabaseName($databaseName)) {
            throw new \InvalidArgumentException('Invalid database name.');
        }

        DB::connection('mysql')->statement(
            "DROP DATABASE IF EXISTS `{$databaseName}`"
        );
    }

    /**
     * Build a unique tenant host from the barangay name: {slug}.{tenant_domain_suffix}
     * If taken, tries {slug}-2, {slug}-3, …
     */
    public function generateUniqueTenantDomain(string $barangayName): string
    {
        $suffix = (string) config('tenancy.tenant_domain_suffix', 'brgy.test');
        $slug = Str::slug($barangayName);
        if ($slug === '') {
            $slug = 'barangay';
        }

        $i = 0;
        do {
            $sub = $i === 0 ? $slug : $slug.'-'.$i;
            $candidate = $sub.'.'.$suffix;
            if (strlen($candidate) > 255) {
                $sub = Str::limit($sub, max(1, 255 - strlen('.'.$suffix)), '');
                $candidate = $sub.'.'.$suffix;
            }
            $exists = Domain::query()->where('domain', strtolower($candidate))->exists();
            $i++;
        } while ($exists && $i < 500);

        if ($exists) {
            throw new \RuntimeException('Could not allocate a unique tenant domain.');
        }

        return strtolower($candidate);
    }

    public function provisionTenant(
        string $name,
        string $domain,
        ?int $planId = null,
        ?string $secretaryEmail = null,
        ?string $secretaryPassword = null,
        ?string $captainEmail = null,
        ?string $captainPassword = null,
    ): Tenant {
        $planId = $planId ?? Plan::query()->where('slug', 'basic')->value('id');
        $database = $this->generateUniqueDatabaseName($name);

        $this->createDatabase($database);

        try {
            $tenant = null;

            DB::connection('mysql')->transaction(function () use ($name, $database, $planId, $domain, &$tenant) {
                $tenant = Tenant::query()->create([
                    'name' => $name,
                    'database' => $database,
                    'status' => 'active',
                    'plan_id' => $planId,
                ]);

                Domain::query()->create([
                    'tenant_id' => $tenant->id,
                    'domain' => Str::lower($domain),
                ]);

                if ($planId) {
                    Subscription::query()->create([
                        'tenant_id' => $tenant->id,
                        'plan_id' => $planId,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => now()->copy()->addYear(),
                    ]);
                }
            });

            $tenant->runTenantMigrations();

            if ($secretaryEmail && $secretaryPassword && $captainEmail && $captainPassword) {
                $this->createInitialOfficerAccounts(
                    $tenant,
                    $name,
                    $secretaryEmail,
                    $secretaryPassword,
                    $captainEmail,
                    $captainPassword,
                );
            }

            return $tenant->fresh(['domains', 'plan']);
        } catch (\Throwable $e) {
            Tenant::query()->where('database', $database)->delete();
            try {
                $this->dropDatabase($database);
            } catch (\Throwable) {
                // best-effort cleanup
            }
            throw $e;
        }
    }

    /**
     * Create Barangay Secretary and Punong Barangay accounts in the tenant database.
     */
    private function createInitialOfficerAccounts(
        Tenant $tenant,
        string $barangayName,
        string $secretaryEmail,
        string $secretaryPassword,
        string $captainEmail,
        string $captainPassword,
    ): void {
        $tenant->configureTenantConnection();

        User::query()->create([
            'name' => "{$barangayName} — Secretary",
            'email' => Str::lower($secretaryEmail),
            'password' => Hash::make($secretaryPassword),
            'role' => TenantRole::Secretary,
        ]);

        User::query()->create([
            'name' => "{$barangayName} — Punong Barangay",
            'email' => Str::lower($captainEmail),
            'password' => Hash::make($captainPassword),
            'role' => TenantRole::Captain,
        ]);
    }

    private function isValidDatabaseName(string $databaseName): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_]+$/', $databaseName);
    }

    /**
     * Build a unique tenant DB name from a hash of the barangay name (opaque, ASCII-safe).
     * MySQL database names are limited to 64 characters; tenant_ + 32 hex = 39 chars.
     */
    private function generateUniqueDatabaseName(string $barangayName): string
    {
        $normalized = trim($barangayName);
        if ($normalized === '') {
            $normalized = 'barangay';
        }

        $hash = substr(hash('sha256', Str::lower($normalized)), 0, 32);
        $base = 'tenant_'.$hash;
        $i = 0;

        do {
            $candidate = $i === 0 ? $base : $base.'_'.$i;
            $existsInTenants = Tenant::query()->where('database', $candidate)->exists();
            $quoted = DB::connection('mysql')->getPdo()->quote($candidate);
            $existsInMysql = DB::connection('mysql')
                ->select("SHOW DATABASES LIKE {$quoted}") !== [];
            $i++;
        } while (($existsInTenants || $existsInMysql) && $i < 500);

        if ($existsInTenants || $existsInMysql) {
            return 'tenant_'.Str::lower(Str::random(32));
        }

        return $candidate;
    }
}
