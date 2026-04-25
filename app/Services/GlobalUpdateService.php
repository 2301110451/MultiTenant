<?php

namespace App\Services;

use App\Jobs\DispatchGlobalUpdateNotificationsJob;
use App\Models\CentralUser;
use App\Models\SystemVersion;
use App\Models\UpdateAnnouncement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalUpdateService
{
    public function __construct(
        private readonly GitHubService $githubService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     success: bool,
     *     error: string|null,
     *     version?: string|null,
     *     type?: string|null,
     *     github_release_id?: int|null
     * }
     */
    public function publishUpdate(array $payload, CentralUser $actor): array
    {
        try {
            $title = trim((string) ($payload['title'] ?? ''));
            $description = trim((string) ($payload['description'] ?? ''));
            $scope = (string) ($payload['scope'] ?? 'all_tenants');
            $selectedTenantIds = collect($payload['selected_tenant_ids'] ?? [])
                ->map(static fn ($id) => (int) $id)
                ->filter(static fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $selectedType = strtolower(trim((string) ($payload['update_type'] ?? '')));
            $type = $this->resolvePublishType($selectedType, "{$title} {$description}");
            $version = $this->generateNextVersion($type);
            $audience = $scope === 'selected' ? 'selected' : 'all';
            $targetedTenantIds = $scope === 'selected' ? $selectedTenantIds : null;
            $githubReleaseId = null;

            DB::connection('mysql')->transaction(function () use (
                $actor,
                $audience,
                $description,
                &$githubReleaseId,
                $targetedTenantIds,
                $title,
                $type,
                $version
            ): void {
                $update = UpdateAnnouncement::query()->create([
                    'title' => $title,
                    'version' => $version,
                    'update_type' => $type,
                    'source' => 'manual',
                    'message' => $description,
                    'audience' => $audience,
                    'targeted_tenant_ids' => $targetedTenantIds,
                    'is_active' => true,
                    'published_at' => now(),
                    'published_by' => $actor->id,
                ]);

                $release = $this->githubService->createRelease($version, $title, $description);
                if (! $release['success']) {
                    throw new \RuntimeException($release['error'] ?? 'GitHub release creation failed.');
                }

                $releaseData = $release['data'] ?? [];
                $update->forceFill([
                    'github_release_id' => (int) ($releaseData['id'] ?? 0) ?: null,
                    'github_tag_name' => (string) ($releaseData['tag_name'] ?? $version),
                    'synced_at' => now(),
                ])->save();
                $githubReleaseId = (int) ($releaseData['id'] ?? 0) ?: null;

                SystemVersion::query()->updateOrCreate(
                    ['version' => $version],
                    [
                        'release_type' => strtolower($type),
                        'notes' => $description,
                        'released_at' => now(),
                        'released_by' => $actor->id,
                    ]
                );
            });

            DispatchGlobalUpdateNotificationsJob::dispatch(
                $title,
                $version,
                $description,
                $scope === 'selected' ? $selectedTenantIds : null
            );

            return [
                'success' => true,
                'error' => null,
                'version' => $version,
                'type' => $type,
                'github_release_id' => $githubReleaseId,
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to publish global update.', [
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Unable to publish update. GitHub/DB operation failed.',
            ];
        }
    }

    /**
     * @return array{success: bool, synced: int, error: string|null}
     */
    public function syncFromGitHub(CentralUser $actor): array
    {
        $releasesResult = $this->githubService->getReleases();
        if (! $releasesResult['success']) {
            return [
                'success' => false,
                'synced' => 0,
                'error' => $releasesResult['error'] ?? 'GitHub sync failed.',
            ];
        }

        $syncedCount = 0;

        foreach ($releasesResult['data'] as $release) {
            if (! is_array($release)) {
                continue;
            }

            try {
                DB::connection('mysql')->transaction(function () use ($actor, $release, &$syncedCount): void {
                    $version = trim((string) ($release['tag_name'] ?? ''));
                    if ($version === '') {
                        return;
                    }

                    $title = trim((string) ($release['name'] ?? "Release {$version}"));
                    $description = trim((string) ($release['body'] ?? ''));
                    $description = $description !== '' ? $description : 'Maintenance release from GitHub.';
                    $releaseType = $this->detectType("{$title} {$description}");
                    if (trim((string) ($release['body'] ?? '')) === '') {
                        $releaseType = 'MAINTENANCE';
                    }

                    $githubReleaseId = (int) ($release['id'] ?? 0);
                    $announcement = UpdateAnnouncement::query()
                        ->where('github_release_id', $githubReleaseId)
                        ->first();

                    if ($announcement === null) {
                        $announcement = UpdateAnnouncement::query()
                            ->where('version', $version)
                            ->first();
                    }

                    if ($announcement !== null && $announcement->source === 'manual') {
                        $announcement->forceFill([
                            'github_release_id' => $announcement->github_release_id ?: $githubReleaseId,
                            'github_tag_name' => $announcement->github_tag_name ?: $version,
                            'synced_at' => now(),
                        ])->save();
                    } else {
                        $announcement = $announcement ?? new UpdateAnnouncement;
                        $announcement->fill([
                            'title' => $title,
                            'version' => $version,
                            'update_type' => $releaseType,
                            'source' => 'github',
                            'github_release_id' => $githubReleaseId ?: null,
                            'github_tag_name' => $version,
                            'message' => $description,
                            'audience' => $announcement->audience ?? 'all',
                            'targeted_tenant_ids' => $announcement->targeted_tenant_ids,
                            'is_active' => true,
                            'published_at' => $announcement->published_at ?? now(),
                            'published_by' => $announcement->published_by ?? $actor->id,
                            'synced_at' => now(),
                        ]);
                        $announcement->save();
                    }

                    SystemVersion::query()->updateOrCreate(
                        ['version' => $version],
                        [
                            'release_type' => strtolower($releaseType),
                            'notes' => $description,
                            'released_at' => now(),
                            'released_by' => $actor->id,
                        ]
                    );

                    $syncedCount++;
                });
            } catch (\Throwable $exception) {
                Log::warning('Failed syncing a GitHub release into local updates.', [
                    'release_id' => $release['id'] ?? null,
                    'tag_name' => $release['tag_name'] ?? null,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return [
            'success' => true,
            'synced' => $syncedCount,
            'error' => null,
        ];
    }

    private function detectType(string $text): string
    {
        $value = strtolower($text);

        if (str_contains($value, 'breaking')) {
            return 'BREAKING';
        }
        if (str_contains($value, 'fix')) {
            return 'FIX';
        }
        if (str_contains($value, 'feature') || str_contains($value, 'add')) {
            return 'FEATURE';
        }

        return 'IMPROVEMENT';
    }

    private function resolvePublishType(string $selectedType, string $fallbackText): string
    {
        return match ($selectedType) {
            'feature' => 'FEATURE',
            'security' => 'SECURITY',
            'maintenance' => 'MAINTENANCE',
            default => $this->detectType($fallbackText),
        };
    }

    private function generateNextVersion(string $type): string
    {
        $latestRelease = $this->githubService->getLatestRelease();
        $latestTag = trim((string) ($latestRelease['data']['tag_name'] ?? 'v0.0.0'));
        $allReleases = $this->githubService->getReleases();
        $existingTags = collect($allReleases['data'] ?? [])
            ->map(static fn ($release) => is_array($release) ? trim((string) ($release['tag_name'] ?? '')) : '')
            ->filter()
            ->values()
            ->all();

        if (! preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $latestTag, $matches)) {
            $matches = [0, '0', '0', '0'];
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3];

        if ($type === 'BREAKING') {
            $major++;
            $minor = 0;
            $patch = 0;
        } elseif ($type === 'FEATURE') {
            $minor++;
            $patch = 0;
        } else {
            $patch++;
        }

        $candidate = "v{$major}.{$minor}.{$patch}";
        while (
            UpdateAnnouncement::query()->where('version', $candidate)->exists()
            || in_array($candidate, $existingTags, true)
        ) {
            $patch++;
            $candidate = "v{$major}.{$minor}.{$patch}";
        }

        return $candidate;
    }
}
