@php
    $tenant = \App\Support\Tenancy::currentTenant();
    $subscription = $tenant?->subscription;
    $effectivePlan = $subscription?->plan ?? $tenant?->plan;
@endphp

<x-tenant-guest-layout>
    <div class="animate-in space-y-6">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center ring-1 ring-amber-200/80">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold tracking-widest text-amber-800 uppercase">Portal unavailable</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 tracking-tight">This barangay portal is suspended</h1>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    The central administrator has deactivated access at <span class="font-medium text-slate-800">{{ $domainHost }}</span>.
                    Public sign-in and reservations are paused until the portal is reactivated.
                </p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/80">
                <h2 class="text-sm font-semibold text-slate-800">Subscription on record</h2>
                <p class="text-xs text-slate-500 mt-0.5">Details from the central registry (for your reference).</p>
            </div>
            <dl class="px-5 py-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500 shrink-0">Barangay</dt>
                    <dd class="font-medium text-slate-900 text-right">{{ $tenant?->name ?? '—' }}</dd>
                </div>
                @if($effectivePlan)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Plan</dt>
                        <dd class="font-medium text-slate-900 text-right">{{ $effectivePlan->name }}</dd>
                    </div>
                @endif
                @if($subscription)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Subscription status</dt>
                        <dd class="font-medium text-slate-900 text-right capitalize">{{ $subscription->status }}</dd>
                    </div>
                    @if($subscription->starts_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Period start</dt>
                            <dd class="font-medium text-slate-900 text-right">{{ $subscription->starts_at->timezone(config('app.timezone'))->format('M j, Y') }}</dd>
                        </div>
                    @endif
                    @if($subscription->ends_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Period end</dt>
                            <dd class="font-medium text-slate-900 text-right">{{ $subscription->ends_at->timezone(config('app.timezone'))->format('M j, Y') }}</dd>
                        </div>
                    @endif
                @elseif($effectivePlan)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Note</dt>
                        <dd class="text-slate-600 text-right text-xs leading-relaxed">No active subscription row; plan is assigned directly to the tenant.</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 pt-2 border-t border-slate-100">
                    <dt class="text-slate-500 shrink-0">HTTP status</dt>
                    <dd class="font-mono text-xs text-slate-600">503 Service Unavailable</dd>
                </div>
            </dl>
        </div>

        @if(! empty($subscriptionActionUrl ?? null))
            <div class="rounded-2xl border border-indigo-200 bg-indigo-50/80 p-5 ring-1 ring-indigo-900/5">
                <p class="text-sm font-semibold text-indigo-950">Subscription choice</p>
                <p class="mt-1 text-xs text-indigo-900/80 leading-relaxed">
                    Officers can open a secure form on the central site to request a full <strong>unsubscribe</strong> or an <strong>extension</strong> (same link as in your suspension email).
                </p>
                <a href="{{ $subscriptionActionUrl }}"
                   class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                    Open unsubscribe / extension form
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            </div>
        @endif

        <p class="text-xs text-slate-500 leading-relaxed">
            If you are a barangay officer and believe this is a mistake, contact your central administrator to restore access.
        </p>
    </div>
</x-tenant-guest-layout>
