<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantLoginSelectorController extends Controller
{
    public function index(): View
    {
        $portals = Domain::query()
            ->select('domains.domain', 'tenants.name as tenant_name')
            ->join('tenants', 'tenants.id', '=', 'domains.tenant_id')
            ->where('tenants.status', 'active')
            ->orderBy('tenants.name')
            ->orderBy('domains.domain')
            ->get();

        return view('auth.tenant-login-selector', [
            'portals' => $portals,
        ]);
    }

    public function redirect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = Domain::query()
            ->select('domains.domain')
            ->join('tenants', 'tenants.id', '=', 'domains.tenant_id')
            ->where('tenants.status', 'active')
            ->where('domains.domain', strtolower($data['domain']))
            ->first();

        if (! $domain) {
            return back()
                ->withInput()
                ->withErrors([
                    'domain' => 'Selected barangay portal is unavailable or inactive.',
                ]);
        }

        $loginUrl = rtrim(Tenancy::tenantPortalUrl($domain->domain), '/').'/login';

        return redirect()->to($loginUrl);
    }
}
