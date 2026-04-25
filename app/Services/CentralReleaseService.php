<?php

namespace App\Services;

use App\Models\Release;
use App\Models\ReleaseLog;
use App\Models\Tenant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Process;
use Throwable;

class CentralReleaseService
{
    public function __construct(
        private readonly GitHubService $gitHubService,
    ) {}

    public function syncLatestChanges(): array
    {
        $latestCommit = $this->gitHubService->getLatestCommitOnDefaultBranch();
        if (! $latestCommit['success']) {
            return $this->emptyPayload($latestCommit['error'] ?? 'Unable to fetch latest commit.');
        }

        $sha = (string) Arr::get($latestCommit, 'data.sha', '');
        if ($sha === '') {
            return $this->emptyPayload('Latest commit SHA was empty.');
        }

        $existing = Release::query()->where('source_commit_sha', $sha)->latest('id')->first();
        if ($existing) {
            $payload = $this->toPayload($existing);
            $payload['is_new'] = false;

            return $payload;
        }

        $commit = $this->gitHubService->getCommit($sha);
        if (! $commit['success']) {
            return $this->emptyPayload($commit['error'] ?? 'Unable to fetch changed files for latest commit.');
        }

        $filesAffected = [];
        $changes = [];
        foreach ((array) Arr::get($commit, 'data.files', []) as $file) {
            if (! is_array($file)) {
                continue;
            }

            $filename = (string) ($file['filename'] ?? '');
            if ($filename === '') {
                continue;
            }

            $filesAffected[] = $filename;
            $changes[] = $this->classifyFileChange($file);
        }

        $changesDetected = array_values(array_unique(array_filter($changes)));
        sort($changesDetected);
        $riskLevel = $this->determineRiskLevel($changesDetected);
        $suggestedVersion = $this->suggestNextVersion($changesDetected);
        $releaseType = strtoupper($changesDetected[0] ?? 'IMPROVEMENT');
        $title = $this->buildTitle((string) Arr::get($commit, 'data.commit.message', ''), $sha, $releaseType);
        $releaseNotes = $this->buildReleaseNotes($changesDetected, $filesAffected, $sha);

        $createData = [
            'version' => null,
            'suggested_version' => $suggestedVersion,
            'notes' => $releaseNotes,
            'status' => 'detected',
            'changes_detected' => $changesDetected,
            'files_affected' => $filesAffected,
            'risk_level' => $riskLevel,
            'source_commit_sha' => $sha,
        ];
        if ($this->hasReleaseColumn('title')) {
            $createData['title'] = $title;
        }
        if ($this->hasReleaseColumn('release_type')) {
            $createData['release_type'] = $releaseType;
        }

        $release = Release::query()->create($createData);

        $payload = $this->toPayload($release);
        $payload['is_new'] = true;

        return $payload;
    }

    public function approve(Release $release, int $actorId): Release
    {
        $data = ['status' => 'approved'];
        if ($this->hasReleaseColumn('approved_by')) {
            $data['approved_by'] = $actorId;
        }
        if ($this->hasReleaseColumn('approved_at')) {
            $data['approved_at'] = now();
        }
        if ($this->hasReleaseColumn('rejected_by')) {
            $data['rejected_by'] = null;
        }
        if ($this->hasReleaseColumn('rejected_at')) {
            $data['rejected_at'] = null;
        }

        $release->forceFill($data)->save();

        return $release->refresh();
    }

    public function reject(Release $release, int $actorId): Release
    {
        $data = ['status' => 'rejected'];
        if ($this->hasReleaseColumn('rejected_by')) {
            $data['rejected_by'] = $actorId;
        }
        if ($this->hasReleaseColumn('rejected_at')) {
            $data['rejected_at'] = now();
        }

        $release->forceFill($data)->save();

        return $release->refresh();
    }

    public function markPublished(Release $release, string $version): Release
    {
        $data = [
            'version' => $version,
            'status' => 'published',
        ];
        if ($this->hasReleaseColumn('published_at')) {
            $data['published_at'] = now();
        }

        $release->forceFill($data)->save();

        return $release->refresh();
    }

    public function updateReleaseDetails(Release $release, string $releaseType, string $notes): Release
    {
        $data = [
            'notes' => $notes,
        ];
        if ($this->hasReleaseColumn('release_type')) {
            $data['release_type'] = strtoupper($releaseType);
        }

        $release->forceFill($data)->save();

        return $release->refresh();
    }

    public function applyUpdateForTenant(Tenant $tenant, Release $release): array
    {
        $tenant->configureTenantConnection();
        $transactionOpened = false;

        try {
            $tenantConnection = DB::connection('tenant');
            if (in_array($tenantConnection->getDriverName(), ['mysql', 'pgsql', 'sqlite'], true)) {
                $tenantConnection->beginTransaction();
                $transactionOpened = true;
            }

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--force' => true,
            ]);

            if ($transactionOpened) {
                $tenantConnection->commit();
            }

            $buildResult = Process::timeout(600)->run(PHP_OS_FAMILY === 'Windows'
                ? ['cmd', '/c', 'npm run build']
                : ['npm', 'run', 'build']);

            if (! $buildResult->successful()) {
                throw new \RuntimeException('Build failed: '.trim($buildResult->errorOutput() ?: $buildResult->output()));
            }

            ReleaseLog::query()->create([
                'tenant_id' => $tenant->id,
                'release_id' => $release->id,
                'version_applied' => $release->version ?: $release->suggested_version,
                'status' => 'success',
                'error_message' => null,
            ]);

            return ['success' => true, 'message' => 'Update commands completed successfully.'];
        } catch (Throwable $exception) {
            try {
                $tenantConnection = DB::connection('tenant');
                if ($transactionOpened && $tenantConnection->transactionLevel() > 0) {
                    $tenantConnection->rollBack();
                }
            } catch (Throwable $rollbackException) {
                Log::warning('Tenant rollback during release apply failed.', [
                    'tenant_id' => $tenant->id,
                    'rollback_error' => $rollbackException->getMessage(),
                ]);
            }

            Log::error('Tenant release update failed.', [
                'tenant_id' => $tenant->id,
                'release_id' => $release->id,
                'error' => $exception->getMessage(),
            ]);

            ReleaseLog::query()->create([
                'tenant_id' => $tenant->id,
                'release_id' => $release->id,
                'version_applied' => $release->version ?: $release->suggested_version,
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Update failed safely. No automatic changes were forced.'];
        }
    }

    private function classifyFileChange(array $file): string
    {
        $filename = (string) ($file['filename'] ?? '');
        $status = strtolower((string) ($file['status'] ?? ''));
        $changes = (int) ($file['changes'] ?? 0);

        if ($status === 'added' && str_starts_with($filename, 'app/')) {
            return 'feature';
        }

        foreach (['routes/web.php', 'app/Http/Middleware/', 'app/Models/', 'config/', 'database/migrations/'] as $target) {
            if (str_starts_with($filename, $target) || $filename === rtrim($target, '/')) {
                return 'breaking';
            }
        }

        return $changes <= 25 ? 'fix' : 'feature';
    }

    private function determineRiskLevel(array $changesDetected): string
    {
        if (in_array('breaking', $changesDetected, true)) {
            return 'high';
        }
        if (in_array('feature', $changesDetected, true)) {
            return 'medium';
        }

        return 'low';
    }

    private function suggestNextVersion(array $changesDetected): string
    {
        $latest = $this->gitHubService->getLatestRelease();
        $tag = (string) Arr::get($latest, 'data.tag_name', 'v0.0.0');

        if (! preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $tag, $matches)) {
            $matches = ['v0.0.0', '0', '0', '0'];
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3];

        if (in_array('breaking', $changesDetected, true)) {
            $major++;
            $minor = 0;
            $patch = 0;
        } elseif (in_array('feature', $changesDetected, true)) {
            $minor++;
            $patch = 0;
        } else {
            $patch += 2;
        }

        return sprintf('v%d.%d.%d', $major, $minor, $patch);
    }

    private function buildReleaseNotes(array $changesDetected, array $filesAffected, string $sha): string
    {
        $summary = 'Summary: Detected '.count($filesAffected).' changed file(s) from commit '.substr($sha, 0, 12).'.';
        $changes = 'Changes: '.($changesDetected === [] ? 'none detected' : implode(', ', $changesDetected)).'.';
        $files = $filesAffected === []
            ? 'Affected files: none'
            : 'Affected files: '.implode(', ', array_slice($filesAffected, 0, 20)).(count($filesAffected) > 20 ? ', ...' : '');

        return $summary.PHP_EOL.$changes.PHP_EOL.$files;
    }

    private function buildTitle(string $commitMessage, string $sha, string $releaseType): string
    {
        $commitMessage = trim($commitMessage);
        if ($commitMessage !== '') {
            return mb_strimwidth($commitMessage, 0, 150, '...');
        }

        return sprintf('%s update %s', $releaseType, substr($sha, 0, 8));
    }

    private function toPayload(Release $release): array
    {
        return [
            'release_id' => $release->id,
            'suggested_version' => $release->suggested_version,
            'changes_detected' => $release->changes_detected ?? [],
            'files_affected' => $release->files_affected ?? [],
            'release_notes' => (string) $release->notes,
            'risk_level' => (string) $release->risk_level,
        ];
    }

    private function emptyPayload(string $message): array
    {
        return [
            'release_id' => null,
            'is_new' => false,
            'suggested_version' => null,
            'changes_detected' => [],
            'files_affected' => [],
            'release_notes' => $message,
            'risk_level' => 'unknown',
        ];
    }

    private function hasReleaseColumn(string $column): bool
    {
        static $cache = [];
        if (array_key_exists($column, $cache)) {
            return $cache[$column];
        }

        try {
            $cache[$column] = Schema::connection('mysql')->hasColumn('releases', $column);
        } catch (Throwable) {
            $cache[$column] = false;
        }

        return $cache[$column];
    }
}
