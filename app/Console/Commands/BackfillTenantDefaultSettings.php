<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Console\Command;
use Throwable;

/**
 * Backfill a default TenantSetting row for any tenant that does not have one.
 *
 * This command is non-destructive:
 *  - If a tenant already has a TenantSetting row, it is SKIPPED entirely.
 *  - No existing settings are overwritten.
 *  - Safe to run multiple times (idempotent).
 *
 * Usage:
 *   php artisan tenants:backfill-settings
 *   php artisan tenants:backfill-settings --dry-run
 */
class BackfillTenantDefaultSettings extends Command
{
    protected $signature = 'tenants:backfill-settings
                            {--dry-run : List tenants that would be updated without making changes}';

    protected $description = 'Seed a default TenantSetting row for any tenant that does not have one yet (non-destructive, idempotent).';

    public function handle(): int
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found.');

            return self::SUCCESS;
        }

        $dryRun  = (bool) $this->option('dry-run');
        $seeded  = 0;
        $skipped = 0;
        $failed  = 0;

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes will be written.');
        }

        $this->newLine();

        foreach ($tenants as $tenant) {
            try {
                // Switch to this tenant's database connection.
                $tenant->configureTenantConnection();

                $exists = TenantSetting::query()->exists();

                if ($exists) {
                    $this->line("  <fg=gray>SKIP</>    {$tenant->name} <fg=gray>(id={$tenant->id})</> — settings row already exists");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  <fg=cyan>PENDING</>  {$tenant->name} <fg=gray>(id={$tenant->id})</> — would seed default row");
                    $seeded++;
                    continue;
                }

                TenantSetting::create([
                    'branding_name'            => null,
                    'accent_color'             => null,
                    'background_color'         => null,
                    'sidebar_background_color' => null,
                    'compact_layout'           => false,
                    'module_toggles'           => [],
                ]);

                $this->line("  <fg=green>SEEDED</>   {$tenant->name} <fg=gray>(id={$tenant->id})</>");
                $seeded++;
            } catch (Throwable $e) {
                $this->line("  <fg=red>FAILED</>   {$tenant->name} <fg=gray>(id={$tenant->id})</>: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete. Would seed: {$seeded} tenant(s). Would skip: {$skipped} tenant(s) (already have settings).");
        } else {
            $this->info("Done. Seeded: {$seeded} | Skipped: {$skipped} | Failed: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
