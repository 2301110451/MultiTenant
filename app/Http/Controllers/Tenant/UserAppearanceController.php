<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserAppearanceController extends Controller
{
    public function edit(Request $request): RedirectResponse
    {
        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Personal display customization is disabled. Ask the tenant admin to update Portal settings.');
    }

    public function update(Request $request): RedirectResponse
    {
        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Personal display customization is disabled. Ask the tenant admin to update Portal settings.');
    }
}
