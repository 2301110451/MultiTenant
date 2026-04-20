<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        Gate::forUser($request->user('tenant'))->authorize('viewAny', SupportTicket::class);

        $tenant = Tenancy::currentTenant();
        $tickets = SupportTicket::query()->where('tenant_id', $tenant?->id)->latest()->paginate(20);

        return view('tenant.support.index', compact('tickets'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::forUser($request->user('tenant'))->authorize('create', SupportTicket::class);
        $tenant = Tenancy::currentTenant();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $user = $request->user('tenant');
        SupportTicket::query()->create([
            'tenant_id' => (int) $tenant?->id,
            'requester_name' => (string) $user?->name,
            'requester_email' => (string) $user?->email,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('tenant.support.index')->with('status', 'Support ticket submitted.');
    }
}
