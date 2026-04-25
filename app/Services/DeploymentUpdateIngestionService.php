<?php

namespace App\Services;

use App\Jobs\AnalyzeUpdateEventJob;
use App\Models\UpdateEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DeploymentUpdateIngestionService
{
    public function __construct(
        private readonly GitHubService $gitHubService,
    ) {}

    public function syncIfDue(): void
    {
        if (! $this->canRun()) {
            return;
        }

        $lock = Cache::lock('deployments:auto-sync:lock', 20);
        if (! $lock->get()) {
            return;
        }

        try {
            $now = now()->timestamp;
            $lastCommitSync = (int) Cache::get('deployments:auto-sync:last-commit-sync', 0);
            $lastReleaseSync = (int) Cache::get('deployments:auto-sync:last-release-sync', 0);

            if (($now - $lastCommitSync) >= 60) {
                $this->pollLatestCommit();
                Cache::forever('deployments:auto-sync:last-commit-sync', $now);
            }

            if (($now - $lastReleaseSync) >= 300) {
                $this->pollRecentReleases();
                Cache::forever('deployments:auto-sync:last-release-sync', $now);
            }
        } catch (\Throwable $exception) {
            Log::warning('Deployment auto-sync failed.', [
                'message' => $exception->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }

    public function pollLatestCommit(): int
    {
        if (! $this->canRun()) {
            return 0;
        }

        $result = $this->gitHubService->getLatestCommitOnDefaultBranch();
        if (! $result['success']) {
            return 0;
        }

        $sha = trim((string) ($result['data']['sha'] ?? ''));
        if ($sha === '') {
            return 0;
        }

        $deliveryId = "poll-commit-{$sha}";
        if (UpdateEvent::query()->where('delivery_id', $deliveryId)->exists()) {
            return 0;
        }

        $commitResult = $this->gitHubService->getCommit($sha);
        if (! $commitResult['success']) {
            return 0;
        }

        $files = collect($commitResult['data']['files'] ?? [])
            ->filter(fn ($item): bool => is_array($item))
            ->map(static fn (array $file): string => trim((string) ($file['filename'] ?? '')))
            ->filter()
            ->values()
            ->all();

        $event = UpdateEvent::query()->create([
            'source' => 'github_polling',
            'delivery_id' => $deliveryId,
            'event_type' => 'push',
            'ref' => 'refs/heads/main',
            'commit_sha' => $sha,
            'tag' => null,
            'payload' => $commitResult['data'],
            'normalized' => [
                'kind' => 'commit',
                'ref' => 'refs/heads/main',
                'commit_sha' => $sha,
                'tag' => null,
                'files' => $files,
                'commit_count' => 1,
                'head_commit_message' => (string) ($commitResult['data']['commit']['message'] ?? ''),
                'pusher' => (string) ($commitResult['data']['author']['login'] ?? ''),
            ],
            'status' => 'received',
            'received_at' => now(),
        ]);

        $this->analyzeEvent($event->id);

        return 1;
    }

    public function pollRecentReleases(): int
    {
        if (! $this->canRun()) {
            return 0;
        }

        $result = $this->gitHubService->getReleases();
        if (! $result['success']) {
            return 0;
        }

        // Import only the latest release to avoid flooding the queue with old history.
        $latestRelease = $result['data'][0] ?? null;
        if (! is_array($latestRelease)) {
            return 0;
        }

        $releaseId = (string) ($latestRelease['id'] ?? '');
        $tagName = trim((string) ($latestRelease['tag_name'] ?? ''));
        if ($releaseId === '' || $tagName === '') {
            return 0;
        }

        $deliveryId = "poll-release-{$releaseId}";
        if (UpdateEvent::query()->where('delivery_id', $deliveryId)->exists()) {
            return 0;
        }

        $event = UpdateEvent::query()->create([
            'source' => 'github_polling',
            'delivery_id' => $deliveryId,
            'event_type' => 'release',
            'tag' => $tagName,
            'commit_sha' => (string) ($latestRelease['target_commitish'] ?? ''),
            'payload' => $latestRelease,
            'normalized' => [
                'kind' => 'release',
                'ref' => null,
                'commit_sha' => (string) ($latestRelease['target_commitish'] ?? ''),
                'tag' => $tagName,
                'files' => [],
                'release_name' => (string) ($latestRelease['name'] ?? ''),
            ],
            'status' => 'received',
            'received_at' => now(),
        ]);

        $this->analyzeEvent($event->id);

        return 1;
    }

    private function canRun(): bool
    {
        if (! Schema::connection('mysql')->hasTable('update_events')) {
            return false;
        }

        return trim((string) config('services.github.token', '')) !== ''
            && trim((string) config('services.github.owner', '')) !== ''
            && trim((string) config('services.github.repo', '')) !== '';
    }

    private function analyzeEvent(int $eventId): void
    {
        if ((bool) config('deployments.process_events_inline', true)) {
            AnalyzeUpdateEventJob::dispatchSync($eventId);

            return;
        }

        AnalyzeUpdateEventJob::dispatch($eventId)->onQueue('default');
    }
}
