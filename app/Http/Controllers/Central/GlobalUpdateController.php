<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\Tenant;
use App\Models\UpdateAnnouncement;
use App\Services\GlobalUpdateAuditLogger;
use App\Services\GlobalUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalUpdateController extends Controller
{
    public function __construct(
        private readonly GlobalUpdateService $globalUpdateService,
        private readonly GlobalUpdateAuditLogger $auditLogger,
    ) {}

    public function index(): View
    {
        $updates = UpdateAnnouncement::query()
            ->whereNotNull('version')
            ->latest('published_at')
            ->latest('id')
            ->paginate(20);

        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name']);

        return view('central.global-updates.index', [
            'updates' => $updates,
            'tenants' => $tenants,
        ]);
    }

    public function publish(Request $request): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:8000'],
            'update_type' => ['required', 'in:feature,security,maintenance'],
            'scope' => ['required', 'in:all_tenants,selected'],
            'selected_tenant_ids' => ['nullable', 'array'],
            'selected_tenant_ids.*' => ['integer', 'exists:tenants,id'],
        ]);

        if (($data['scope'] ?? null) === 'selected' && empty($data['selected_tenant_ids'])) {
            $this->auditLogger->log(
                $request,
                $actor,
                'global_update.publish',
                'failed',
                'Selected scope requires tenant selection.',
                (string) ($data['scope'] ?? null),
                strtoupper((string) ($data['update_type'] ?? ''))
            );

            return back()->withErrors([
                'selected_tenant_ids' => 'Select at least one tenant when scope is selected.',
            ])->withInput();
        }

        $result = $this->globalUpdateService->publishUpdate($data, $actor);
        if (! $result['success']) {
            $this->auditLogger->log(
                $request,
                $actor,
                'global_update.publish',
                'failed',
                (string) ($result['error'] ?? 'Publishing failed.'),
                (string) ($data['scope'] ?? null),
                strtoupper((string) ($data['update_type'] ?? ''))
            );

            return back()->with('error', $result['error'] ?? 'Publishing failed.')->withInput();
        }

        $this->auditLogger->log(
            $request,
            $actor,
            'global_update.publish',
            'success',
            'Global update published successfully.',
            (string) ($data['scope'] ?? null),
            (string) ($result['type'] ?? strtoupper((string) ($data['update_type'] ?? ''))),
            (string) ($result['version'] ?? null),
            isset($result['github_release_id']) ? (int) $result['github_release_id'] : null,
            [
                'selected_tenant_ids_count' => is_array($data['selected_tenant_ids'] ?? null)
                    ? count($data['selected_tenant_ids'])
                    : 0,
            ]
        );

        return redirect()
            ->route($this->successRedirectRoute($request))
            ->with('success', 'Global update published. GitHub release created.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $result = $this->globalUpdateService->syncFromGitHub($actor);
        if (! $result['success']) {
            $this->auditLogger->log(
                $request,
                $actor,
                'global_update.sync',
                'failed',
                (string) ($result['error'] ?? 'Sync failed.')
            );

            return back()->with('error', $result['error'] ?? 'Sync failed.');
        }

        $this->auditLogger->log(
            $request,
            $actor,
            'global_update.sync',
            'success',
            'GitHub releases synced.',
            null,
            null,
            null,
            null,
            ['synced_count' => (int) ($result['synced'] ?? 0)]
        );

        return redirect()
            ->route($this->successRedirectRoute($request))
            ->with('success', "GitHub releases synced. Updated: {$result['synced']}.");
    }

    private function successRedirectRoute(Request $request): string
    {
        return $request->boolean('return_to_system_versions')
            ? 'central.system-versions.index'
            : 'central.global-updates.index';
    }
}
