<?php

use App\Models\SystemVersion;
use App\Models\Tenant;
use App\Models\UpdateEvent;
use App\Jobs\AnalyzeUpdateEventJob;
use App\Services\GitHubService;
use App\Services\DeploymentSnapshotService;
use App\Services\DeploymentUpdateIngestionService;
use App\Services\SafeRollbackService;
use App\Models\DeploymentRun;
use App\Models\DeploymentSnapshot;
use App\Support\TenantGoogleOAuthRedirectUri;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('google:oauth-diagnose', function () {
    $this->info('Google OAuth — values Laravel will send as redirect_uri (must match Authorized redirect URIs exactly).');
    $this->newLine();

    $configured = trim((string) config('services.google.redirect', ''));
    if ($configured !== '') {
        $this->line('GOOGLE_REDIRECT_URI is set in .env → Socialite uses this only:');
        $this->warn('  '.TenantGoogleOAuthRedirectUri::normalize($configured));
        $this->comment('Add that exact URI under APIs & Services → Credentials → OAuth 2.0 Client → Authorized redirect URIs.');
    } else {
        $this->comment('GOOGLE_REDIRECT_URI is empty → app builds loopback URI for *.localhost / 127.0.0.1.');
    }

    $this->newLine();
    $suffix = (string) config('tenancy.tenant_domain_suffix', 'localhost');
    $slugHost = 'demo.'.$suffix;
    $port = (int) (parse_url((string) config('app.url'), PHP_URL_PORT) ?: 8000);

    $fromTenant = Request::create("http://{$slugHost}:{$port}/auth/google/redirect", 'GET');
    $fromCallback = Request::create("http://127.0.0.1:{$port}/auth/google/callback", 'GET', ['code' => 'x', 'state' => 'y']);

    $u1 = TenantGoogleOAuthRedirectUri::resolve($fromTenant);
    $u2 = TenantGoogleOAuthRedirectUri::resolve($fromCallback);

    $this->line('Simulated tenant start (e.g. http://'.$slugHost.':'.$port.'/login):');
    $this->line('  '.$u1);
    $this->newLine();
    $this->line('Simulated callback (browser returns to 127.0.0.1):');
    $this->line('  '.$u2);
    $this->newLine();

    if ($u1 !== $u2) {
        $this->error('Mismatch: authorize and callback would use different redirect_uri values — token exchange will fail.');
    } else {
        $this->info('Authorize + callback redirect_uri match (required for OAuth code flow).');
        $this->comment('Copy the URI above into Google Cloud Console if it is not listed yet.');
    }

    $cid = (string) config('services.google.client_id', '');
    $secret = (string) config('services.google.client_secret', '');
    $this->newLine();
    $this->line('GOOGLE_CLIENT_ID: '.($cid !== '' ? substr($cid, 0, 24).'…' : '(empty)'));
    $this->line('GOOGLE_CLIENT_SECRET: '.($secret !== '' ? '(set, length '.strlen($secret).')' : '(empty)'));

    return self::SUCCESS;
})->purpose('Print the OAuth redirect_uri Laravel uses — compare with Google Cloud Console');

Artisan::command('mail:test {email? : Inbox to receive the test message}', function (?string $email = null) {
    $driver = (string) config('mail.default');

    if ($driver !== 'smtp') {
        $this->warn('MAIL_MAILER is "'.$driver.'". For real email use MAIL_MAILER=smtp in .env, then php artisan config:clear');

        return self::FAILURE;
    }

    $user = (string) config('mail.mailers.smtp.username');
    $pass = (string) config('mail.mailers.smtp.password');

    if ($user === '' || $pass === '') {
        $this->error('MAIL_USERNAME or MAIL_PASSWORD is empty.');
        $this->line('1. Open .env in the project root.');
        $this->line('2. Set MAIL_USERNAME to your full Gmail address (example: you@gmail.com).');
        $this->line('3. Set MAIL_PASSWORD to a Google App Password (16 characters), not your normal Gmail password.');
        $this->line('   Create one: https://myaccount.google.com/apppasswords (2-Step Verification must be on).');
        $this->line('4. Set MAIL_FROM_ADDRESS to the same Gmail as MAIL_USERNAME.');
        $this->line('5. Run: php artisan config:clear');
        $this->line('6. Run this command again with your email: php artisan mail:test you@gmail.com');

        return self::FAILURE;
    }

    $masked = strlen($user) > 4 ? substr($user, 0, 3).'***'.substr($user, -2) : '***';
    $this->info('Using SMTP as: '.$masked.' (password length: '.strlen($pass).' chars)');

    if ($email === null || $email === '') {
        $this->comment('Config looks OK. Pass an email to send a test message, e.g. php artisan mail:test you@gmail.com');

        return self::SUCCESS;
    }

    try {
        Mail::raw('If you received this, Laravel mail is configured correctly.', function ($message) use ($email) {
            $message->to($email)->subject('Brgy Reservation — mail test');
        });
    } catch (Throwable $e) {
        $this->error($e->getMessage());

        return self::FAILURE;
    }

    $this->info('Test email sent to '.$email.'. Check inbox and spam folder.');

    return self::SUCCESS;
})->purpose('Check SMTP settings and optionally send a test email');

Artisan::command('system:rollback-version {steps=1 : Number of migration batches to rollback}', function (int $steps = 1) {
    if ($steps < 1) {
        $this->error('Steps must be at least 1.');

        return self::FAILURE;
    }

    $currentBatch = (int) DB::table('migrations')->max('batch');
    if ($currentBatch === 0) {
        $this->warn('No migrations found to rollback.');

        return self::SUCCESS;
    }

    $this->warn('Starting rollback. This affects schema and should be used carefully.');
    $rollbackExitCode = (int) $this->call('migrate:rollback', ['--step' => $steps]);
    if ($rollbackExitCode !== self::SUCCESS) {
        $this->error('Rollback command failed.');

        return self::FAILURE;
    }

    $newBatch = (int) DB::table('migrations')->max('batch');
    SystemVersion::query()->create([
        'version' => 'rollback-'.now()->format('YmdHis'),
        'release_type' => 'rollback',
        'notes' => "Rolled back {$steps} migration step(s). Batch {$currentBatch} -> {$newBatch}.",
        'migration_batch' => $newBatch,
        'released_at' => now(),
        'released_by' => 'artisan',
    ]);

    $this->info('Rollback completed and logged in system_versions.');

    return self::SUCCESS;
})->purpose('Rollback migrations and log a system version event');

Artisan::command('system:sync-tenant-migrations', function () {
    $this->warn('Starting tenant-by-tenant migration sync...');
    $total = Tenant::query()->count();
    $ok = 0;
    $failed = [];

    Tenant::query()->orderBy('id')->chunkById(20, function ($tenants) use (&$ok, &$failed): void {
        foreach ($tenants as $tenant) {
            try {
                $tenant->runTenantMigrations();

                $requiredTables = ['roles', 'permissions', 'role_user', 'permission_role', 'permission_user', 'audit_logs', 'tenant_settings'];
                $missing = [];
                foreach ($requiredTables as $table) {
                    if (! Schema::connection('tenant')->hasTable($table)) {
                        $missing[] = $table;
                    }
                }

                if ($missing !== []) {
                    $failed[] = "#{$tenant->id} {$tenant->name} missing tables: ".implode(', ', $missing);

                    continue;
                }

                $ok++;
                $this->line("Synced tenant #{$tenant->id} {$tenant->name}");
            } catch (Throwable $e) {
                report($e);
                $failed[] = "#{$tenant->id} {$tenant->name} failed: {$e->getMessage()}";
            }
        }
    });

    $this->info("Tenant migration sync finished. Successful: {$ok}/{$total}");
    if ($failed !== []) {
        $this->warn('Some tenants need manual follow-up:');
        foreach ($failed as $line) {
            $this->line(' - '.$line);
        }

        return self::FAILURE;
    }

    return self::SUCCESS;
})->purpose('Run and verify tenant migrations for all tenants');

Artisan::command('audit:backfill-tenant-activity', function () {
    $this->warn('Backfilling tenant audit_logs into central tenant_activity_audit_logs...');

    $totalImported = 0;
    $totalSkipped = 0;
    $originalTenantDb = (string) config('database.connections.tenant.database');

    $tenants = Tenant::query()->orderBy('id')->get(['id', 'name', 'database']);

    foreach ($tenants as $tenant) {
        try {
            $tenant->configureTenantConnection();

            if (! Schema::connection('tenant')->hasTable('audit_logs')) {
                $this->line("Skipped tenant #{$tenant->id} {$tenant->name} (audit_logs table not found).");
                continue;
            }

            $userSnapshot = [];
            if (Schema::connection('tenant')->hasTable('users')) {
                $userSnapshot = DB::connection('tenant')
                    ->table('users')
                    ->select(['id', 'name', 'email'])
                    ->get()
                    ->keyBy('id')
                    ->all();
            }

            DB::connection('tenant')
                ->table('audit_logs')
                ->orderBy('id')
                ->chunkById(500, function ($logs) use ($tenant, $userSnapshot, &$totalImported, &$totalSkipped): void {
                    foreach ($logs as $log) {
                        $eventKey = (string) ($log->action ?? 'general.performed');
                        $parts = explode('.', $eventKey, 2);
                        $module = $parts[0] ?? 'general';
                        $action = $parts[1] ?? $eventKey;

                        $existing = DB::connection('mysql')
                            ->table('tenant_activity_audit_logs')
                            ->where('tenant_id', (int) $tenant->id)
                            ->where('event_key', $eventKey)
                            ->where('actor_user_id', $log->actor_user_id)
                            ->where('target_type', $log->target_type)
                            ->where('target_id', $log->target_id)
                            ->where('created_at', $log->created_at)
                            ->exists();

                        if ($existing) {
                            $totalSkipped++;
                            continue;
                        }

                        $metadata = null;
                        if (isset($log->metadata) && $log->metadata !== null && $log->metadata !== '') {
                            $decoded = json_decode((string) $log->metadata, true);
                            $metadata = is_array($decoded) ? $decoded : null;
                        }

                        $actor = null;
                        if ($log->actor_user_id !== null && isset($userSnapshot[$log->actor_user_id])) {
                            $actor = $userSnapshot[$log->actor_user_id];
                        }

                        DB::connection('mysql')->table('tenant_activity_audit_logs')->insert([
                            'tenant_id' => (int) $tenant->id,
                            'actor_type' => $log->actor_user_id ? 'tenant_user' : 'system',
                            'actor_user_id' => $log->actor_user_id,
                            'actor_name' => $actor->name ?? null,
                            'actor_email' => $actor->email ?? null,
                            'module' => $module,
                            'action' => $action,
                            'event_key' => $eventKey,
                            'status' => 'success',
                            'target_type' => $log->target_type,
                            'target_id' => $log->target_id,
                            'target_label' => is_array($metadata) ? ($metadata['target_label'] ?? null) : null,
                            'before_values' => is_array($metadata) ? ($metadata['before_values'] ?? $metadata['before'] ?? $metadata['old_values'] ?? null) : null,
                            'after_values' => is_array($metadata) ? ($metadata['after_values'] ?? $metadata['after'] ?? $metadata['new_values'] ?? null) : null,
                            'metadata' => is_array($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES) : null,
                            'ip_address' => $log->ip_address,
                            'user_agent' => $log->user_agent,
                            'created_at' => $log->created_at,
                            'updated_at' => $log->updated_at ?? $log->created_at,
                        ]);

                        $totalImported++;
                    }
                });

            $this->line("Processed tenant #{$tenant->id} {$tenant->name}");
        } catch (Throwable $e) {
            report($e);
            $this->error("Failed tenant #{$tenant->id} {$tenant->name}: {$e->getMessage()}");
        }
    }

    config(['database.connections.tenant.database' => $originalTenantDb]);
    DB::purge('tenant');

    $this->info("Backfill done. Imported: {$totalImported}; Skipped (duplicates): {$totalSkipped}");

    return self::SUCCESS;
})->purpose('Backfill tenant audit_logs into central tenant_activity_audit_logs');

Artisan::command('deployments:poll-github-updates', function (DeploymentUpdateIngestionService $ingestionService) {
    if (! Schema::connection('mysql')->hasTable('update_events')) {
        $this->error('Missing table: update_events. Run php artisan migrate first.');

        return self::FAILURE;
    }

    $this->warn('Polling GitHub releases as webhook fallback...');
    $created = $ingestionService->pollRecentReleases();

    $this->info("Polling finished. New normalized update events: {$created}");

    return self::SUCCESS;
})->purpose('Fallback polling: import GitHub releases into update events');

Artisan::command('deployments:poll-github-commits', function (DeploymentUpdateIngestionService $ingestionService) {
    if (! Schema::connection('mysql')->hasTable('update_events')) {
        $this->error('Missing table: update_events. Run php artisan migrate first.');

        return self::FAILURE;
    }

    $this->warn('Polling latest commit from GitHub as webhook fallback...');
    $created = $ingestionService->pollLatestCommit();

    if ($created > 0) {
        $this->info('Created update event from latest commit.');
    } else {
        $this->info('No new commit event created (already imported or unavailable).');
    }

    return self::SUCCESS;
})->purpose('Fallback polling: import latest commit and changed files into update events');

Artisan::command('deployments:create-snapshot
    {version : Semantic version label for immutable snapshot}
    {--artifact-digest= : Artifact digest (sha256:...)}
    {--artifact-uri= : Artifact storage URI}
    {--code-ref= : Commit SHA/tag}
    {--lockfile-hash= : Dependency lockfile hash}
    {--config-hash= : Environment/config hash}', function (DeploymentSnapshotService $snapshotService) {
    $version = trim((string) $this->argument('version'));
    if ($version === '') {
        $this->error('Version is required.');

        return self::FAILURE;
    }

    $snapshot = $snapshotService->createSnapshot(
        $version,
        null,
        $this->option('artifact-digest'),
        $this->option('artifact-uri'),
        $this->option('code-ref'),
        $this->option('lockfile-hash'),
        $this->option('config-hash'),
        ['source' => 'artisan']
    );

    $this->info("Snapshot created: #{$snapshot->id} ({$snapshot->version})");

    return self::SUCCESS;
})->purpose('Create immutable deployment snapshot metadata');

Artisan::command('deployments:monitor-health {run_id : Deployment run id} {--error-rate=0} {--p95=0}', function (
    int $runId,
    SafeRollbackService $rollbackService
) {
    $run = DeploymentRun::query()->find($runId);
    if (! $run instanceof DeploymentRun) {
        $this->error('Deployment run not found.');

        return self::FAILURE;
    }

    $errorRate = (float) $this->option('error-rate');
    $p95 = (int) $this->option('p95');

    $maxErrorRate = (float) config('deployments.auto_rollback.max_error_rate_percent', 5);
    $maxP95 = (int) config('deployments.auto_rollback.max_p95_latency_ms', 1500);

    $run->forceFill([
        'health_metrics' => [
            'error_rate_percent' => $errorRate,
            'p95_latency_ms' => $p95,
        ],
    ])->save();

    $shouldRollback = (bool) config('deployments.auto_rollback.enabled', true)
        && ($errorRate > $maxErrorRate || $p95 > $maxP95);

    if (! $shouldRollback) {
        $this->info('Health is within thresholds. No rollback triggered.');

        return self::SUCCESS;
    }

    try {
        $rollback = $rollbackService->autoRollback(
            $run,
            "Auto rollback due to health breach: error_rate={$errorRate}, p95={$p95}"
        );
    } catch (Throwable $e) {
        $this->error($e->getMessage());

        return self::FAILURE;
    }

    $this->warn("Automatic rollback completed: #{$rollback->id}");

    return self::SUCCESS;
})->purpose('Evaluate health metrics and trigger automatic rollback if thresholds are breached');

Artisan::command('deployments:mark-snapshot-stable {snapshot_id : Snapshot id}', function (int $snapshotId) {
    $snapshot = DeploymentSnapshot::query()->find($snapshotId);
    if (! $snapshot instanceof DeploymentSnapshot) {
        $this->error('Snapshot not found.');

        return self::FAILURE;
    }

    DeploymentSnapshot::query()->where('is_stable', true)->update(['is_stable' => false]);
    $snapshot->forceFill(['is_stable' => true])->save();

    $this->info("Snapshot {$snapshot->version} is now the stable rollback target.");

    return self::SUCCESS;
})->purpose('Mark a snapshot as stable rollback target');

Schedule::command('deployments:poll-github-commits')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('deployments:poll-github-updates')
    ->everyFiveMinutes()
    ->withoutOverlapping();
