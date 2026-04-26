<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\GlobalUpdateAuditLog;
use App\Models\Release;
use App\Models\SystemVersion;
use App\Services\CentralReleaseService;
use App\Services\GitHubService;
use App\Services\GlobalUpdateAuditLogger;
use App\Services\GlobalUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReleaseController extends Controller
{
    public function __construct(
        private readonly CentralReleaseService $centralReleaseService,
        private readonly GlobalUpdateService $globalUpdateService,
        private readonly GlobalUpdateAuditLogger $auditLogger,
        private readonly GitHubService $gitHubService,
    ) {}

    public function index(Request $request): View
    {
        $selectedReleaseId = (int) $request->query('release');
        $selectedRelease = $selectedReleaseId > 0
            ? Release::query()->where('status', 'approved')->find($selectedReleaseId)
            : null;
        $releases = Release::query()->latest('id')->paginate(15)->withQueryString();
        $versions = SystemVersion::query()->latest('released_at')->latest('id')->limit(20)->get();
        $releaseAudits = GlobalUpdateAuditLog::query()->with('actor:id,name')->latest('id')->limit(20)->get();

        $localReleaseQuery = Release::query()
            ->whereNotNull('version')
            ->where('status', 'published');
        if (Schema::connection('mysql')->hasColumn('releases', 'published_at')) {
            $localReleaseQuery->latest('published_at');
        }
        $localLatestVersion = $localReleaseQuery
            ->latest('id')
            ->value('version');
        if (! $localLatestVersion) {
            $localLatestVersion = SystemVersion::query()->latest('released_at')->latest('id')->value('version');
        }

        $latestDetectedRelease = Release::query()->latest('id')->first();
        $githubLatestVersion = (string) (
            $latestDetectedRelease?->suggested_version
            ?: $latestDetectedRelease?->version
            ?: ''
        );
        $hasNewGithubRelease = $this->hasGithubUpdate($localLatestVersion, $githubLatestVersion !== '' ? $githubLatestVersion : null);
        // Avoid slow external API calls on page load.
        // GitHub checks happen when the user explicitly clicks "Sync from GitHub".
        $githubLatestCommitSha = null;
        $hasNewGithubCommit = false;

        $githubOwner = trim((string) config('services.github.owner', ''));
        $githubRepo = trim((string) config('services.github.repo', ''));
        $githubBaseUrl = ($githubOwner !== '' && $githubRepo !== '')
            ? "https://github.com/{$githubOwner}/{$githubRepo}"
            : null;

        return view('central.releases.index', [
            'releases' => $releases,
            'selectedRelease' => $selectedRelease,
            'versions' => $versions,
            'releaseAudits' => $releaseAudits,
            'localLatestVersion' => $localLatestVersion ?: null,
            'githubLatestVersion' => $githubLatestVersion !== '' ? $githubLatestVersion : null,
            'hasNewGithubRelease' => $hasNewGithubRelease,
            'githubLatestCommitSha' => $githubLatestCommitSha !== '' ? substr($githubLatestCommitSha, 0, 8) : null,
            'hasNewGithubCommit' => $hasNewGithubCommit,
            'githubBaseUrl' => $githubBaseUrl,
        ]);
    }

    public function detect(): JsonResponse
    {
        return response()->json($this->centralReleaseService->syncLatestChanges());
    }

    public function detectAndStore(Request $request): RedirectResponse
    {
        try {
            $result = $this->centralReleaseService->syncLatestChanges();
            $actor = $request->user('web');
            if ($actor instanceof CentralUser) {
                $this->auditLogger->log(
                    $request,
                    $actor,
                    'release.sync',
                    'success',
                    'Release sync from GitHub executed.',
                    'all_tenants',
                    null,
                    (string) ($result['suggested_version'] ?? null),
                    null,
                    [
                        'record' => 'sync_from_github',
                        'release_id' => $result['release_id'] ?? null,
                        'changes_detected' => $result['changes_detected'] ?? [],
                    ]
                );
            }

            return redirect()
                ->route('central.releases.index')
                ->with(
                    'success',
                    ($result['is_new'] ?? false)
                        ? 'GitHub sync completed. New update detected. Suggested version: '.($result['suggested_version'] ?? 'N/A')
                        : 'GitHub sync completed. No new release update detected.'
                );
        } catch (\Throwable $exception) {
            Log::error('Release sync failed unexpectedly.', ['message' => $exception->getMessage()]);

            return redirect()->route('central.releases.index')->with('error', 'Release sync failed. Please try again.');
        }
    }

    public function approve(Request $request, Release $release): RedirectResponse
    {
        $actor = $request->user('web');
        $actorId = (int) ($actor?->id ?? 0);
        if ($actorId <= 0) {
            abort(403);
        }

        try {
            $this->centralReleaseService->approve($release, $actorId);
            $this->logAudit($request, $actor, 'release.approve', 'success', 'Release approved for version logging.', $release);

            return redirect()
                ->route('central.releases.index', ['release' => $release->id])
                ->with('success', 'Release approved. Fill out Log System Version below.');
        } catch (\Throwable $exception) {
            Log::error('Release approve failed.', ['release_id' => $release->id, 'message' => $exception->getMessage()]);

            return redirect()->route('central.releases.index')->with('error', 'Unable to approve release right now.');
        }
    }

    public function reject(Request $request, Release $release): RedirectResponse
    {
        $actor = $request->user('web');
        $actorId = (int) ($actor?->id ?? 0);
        if ($actorId <= 0) {
            abort(403);
        }

        try {
            $this->centralReleaseService->reject($release, $actorId);
            $this->logAudit($request, $actor, 'release.reject', 'success', 'Release rejected.', $release);

            return redirect()->route('central.releases.index')->with('success', 'Release rejected.');
        } catch (\Throwable $exception) {
            Log::error('Release reject failed.', ['release_id' => $release->id, 'message' => $exception->getMessage()]);

            return redirect()->route('central.releases.index')->with('error', 'Unable to reject release right now.');
        }
    }

    public function saveVersion(Request $request, Release $release): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        if ($release->status !== 'approved') {
            return redirect()->route('central.releases.index')->with('error', 'Only approved releases can be logged.');
        }

        $data = $request->validate([
            'version' => ['required', 'string', 'max:50'],
            'release_type' => ['required', 'in:feature,security,maintenance'],
            'notes' => ['required', 'string', 'max:8000'],
        ]);

        try {
            $publish = $this->globalUpdateService->publishUpdate([
                'title' => (string) ($release->title ?: 'Central Release Update'),
                'description' => (string) $data['notes'],
                'update_type' => (string) $data['release_type'],
                'scope' => 'all_tenants',
                'selected_tenant_ids' => [],
            ], $actor);

            if (! $publish['success']) {
                $this->logAudit($request, $actor, 'release.save-version', 'failed', (string) ($publish['error'] ?? 'Publishing failed'), $release);

                return redirect()
                    ->route('central.releases.index', ['release' => $release->id])
                    ->with('error', (string) ($publish['error'] ?? 'Unable to save version.'));
            }

            $version = (string) ($publish['version'] ?? $data['version']);
            $this->centralReleaseService->updateReleaseDetails(
                $release,
                (string) $data['release_type'],
                (string) $data['notes']
            );
            $this->centralReleaseService->markPublished($release, $version);

            $this->logAudit(
                $request,
                $actor,
                'release.save-version',
                'success',
                'Version logged and published. Tenant notifications dispatched.',
                $release,
                $version,
                isset($publish['github_release_id']) ? (int) $publish['github_release_id'] : null
            );

            return redirect()
                ->route('central.releases.index')
                ->with('success', 'Version saved. Tenants/users are notified and GitHub release/tag was created.');
        } catch (\Throwable $exception) {
            Log::error('Release save-version failed unexpectedly.', ['release_id' => $release->id, 'message' => $exception->getMessage()]);

            $this->logAudit($request, $actor, 'release.save-version', 'failed', 'Unexpected error during save-version.', $release);

            return redirect()
                ->route('central.releases.index', ['release' => $release->id])
                ->with('error', 'Unable to save version right now. No destructive changes were applied.');
        }
    }

    private function logAudit(
        Request $request,
        mixed $actor,
        string $action,
        string $status,
        string $message,
        Release $release,
        ?string $version = null,
        ?int $githubReleaseId = null
    ): void {
        if (! $actor instanceof CentralUser) {
            return;
        }

        $this->auditLogger->log(
            $request,
            $actor,
            $action,
            $status,
            $message,
            'all_tenants',
            (string) ($release->release_type ?? null),
            $version ?? $release->version,
            $githubReleaseId,
            [
                'release_id' => $release->id,
                'source_commit_sha' => $release->source_commit_sha,
            ]
        );
    }

    private function hasGithubUpdate(?string $localVersion, ?string $githubVersion): bool
    {
        $local = $this->normalizeVersion($localVersion);
        $github = $this->normalizeVersion($githubVersion);

        if ($local === null || $github === null) {
            return false;
        }

        return version_compare($github, $local, '>');
    }

    private function normalizeVersion(?string $version): ?string
    {
        $value = trim((string) $version);
        if ($value === '') {
            return null;
        }

        $value = ltrim($value, 'vV');
        if (! preg_match('/^\d+\.\d+\.\d+$/', $value)) {
            return null;
        }

        return $value;
    }
}
