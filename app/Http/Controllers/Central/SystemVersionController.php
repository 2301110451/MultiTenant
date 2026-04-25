<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\GlobalUpdateAuditLog;
use App\Models\SystemVersion;
use App\Models\Tenant;
use App\Models\UpdateAnnouncement;
use App\Services\GitHubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SystemVersionController extends Controller
{
    public function index(): View
    {
        $versions = SystemVersion::query()->latest('released_at')->latest()->paginate(20);
        $actor = auth('web')->user();
        $isSuperAdmin = (bool) ($actor?->isSuperAdmin());

        /** @var Collection<int, UpdateAnnouncement> $globalUpdates */
        $globalUpdates = collect();
        /** @var Collection<int, Tenant> $tenants */
        $tenants = collect();
        /** @var Collection<int, GlobalUpdateAuditLog> $globalUpdateAuditLogs */
        $globalUpdateAuditLogs = collect();

        if ($isSuperAdmin) {
            $globalUpdates = UpdateAnnouncement::query()
                ->whereNotNull('version')
                ->latest('published_at')
                ->latest('id')
                ->limit(20)
                ->get();

            $tenants = Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name']);

            $globalUpdateAuditLogs = GlobalUpdateAuditLog::query()
                ->with('actor:id,name,email')
                ->latest('id')
                ->limit(30)
                ->get();
        }

        return view('central/system-versions/index', compact('versions', 'globalUpdates', 'tenants', 'globalUpdateAuditLogs'));
    }

    public function store(Request $request, GitHubService $gitHubService): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:50', 'unique:system_versions,version'],
            'release_type' => ['required', 'in:feature,security,hotfix,maintenance'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'migration_batch' => ['nullable', 'string', 'max:100'],
            'publish_to_github' => ['nullable', 'boolean'],
            'github_title' => ['nullable', 'string', 'max:255'],
            'github_notes' => ['nullable', 'string', 'max:8000'],
        ]);

        $publishToGitHub = (bool) ($data['publish_to_github'] ?? false);
        if ($publishToGitHub) {
            $title = trim((string) ($data['github_title'] ?? ''));
            $notes = trim((string) ($data['github_notes'] ?? ''));
            $versionTag = trim((string) ($data['version'] ?? ''));
            $releaseTitle = $title !== '' ? $title : "Release {$versionTag}";
            $releaseNotes = $notes !== '' ? $notes : (string) ($data['notes'] ?? 'System version release');

            $release = $gitHubService->createRelease($versionTag, $releaseTitle, $releaseNotes);
            if (! $release['success']) {
                return back()
                    ->withInput()
                    ->with('error', (string) ($release['error'] ?? 'Failed to create GitHub release.'));
            }
        }

        SystemVersion::query()->create([
            'version' => (string) $data['version'],
            'release_type' => (string) $data['release_type'],
            'notes' => $data['notes'] ?? null,
            'migration_batch' => $data['migration_batch'] ?? null,
            'released_at' => now(),
            'released_by' => $request->user()->id,
        ]);

        $message = $publishToGitHub
            ? 'System version logged and published to GitHub Releases.'
            : 'System version logged.';

        return redirect()->route('central.system-versions.index')->with('success', $message);
    }
}
