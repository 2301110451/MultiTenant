<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Release;
use App\Services\CentralReleaseService;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReleaseController extends Controller
{
    public function __construct(
        private readonly CentralReleaseService $centralReleaseService,
    ) {}

    public function index(): View
    {
        return view('tenant.releases.index', [
            'releases' => Release::query()
                ->whereIn('status', ['published', 'approved'])
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function apply(Release $release): RedirectResponse
    {
        $tenant = Tenancy::currentTenant();
        if (! $tenant || $release->status !== 'published') {
            return redirect()
                ->route('tenant.releases.index')
                ->with('error', 'Release is unavailable for this tenant.');
        }

        $result = $this->centralReleaseService->applyUpdateForTenant($tenant, $release);

        return redirect()
            ->route('tenant.releases.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }
}
