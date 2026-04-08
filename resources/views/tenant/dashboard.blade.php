@php
    $theme = \App\Support\TenantAppearance::theme();
    $tenantPlanModel = $tenant?->subscription?->plan ?? $tenant?->plan;
@endphp
<x-tenant-layout title="Dashboard" breadcrumb="Dashboard">

    <div class="px-6 py-8 sm:px-10 space-y-8">

        {{-- Page heading --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Welcome back, <strong class="text-slate-700 dark:text-slate-200">{{ $user->name }}</strong>
                <span class="text-slate-300 dark:text-slate-600 mx-1">&middot;</span>
                <span class="capitalize">{{ str_replace('_', ' ', $user->role->value) }}</span>
            </p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="t-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pending Reservations</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mt-1.5">{{ $pendingApprovals }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Active Facilities</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mt-1.5">{{ $facilitiesCount }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Subscription</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-1.5">{{ $tenant?->subscription?->plan?->name ?? $tenant?->plan?->name ?? '—' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card p-5 ring-1 {{ $theme['panelRing'] }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Plan Tier</p>
                        <p class="text-sm font-semibold mt-1.5 {{ $theme['breadcrumbAccent'] }}">{{ $theme['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">UI accent matches plan.</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl {{ $theme['brandIcon'] }} flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($user->isSecretary() || $user->isCaptain())
            <div class="t-card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Core Functions</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Management modules for Secretary and Barangay Captain.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Facility Management</h3>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                            <li>&bull; Add facilities</li>
                            <li>&bull; Set maximum capacity</li>
                            <li>&bull; Define reservation rules</li>
                            <li>&bull; Set operating hours</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Equipment Tracking</h3>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                            <li>&bull; Add items and quantity</li>
                            <li>&bull; Track condition</li>
                            <li>&bull; Log borrower</li>
                            <li>&bull; Set penalty fees</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Reservation System</h3>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                            <li>&bull; Real time calendar</li>
                            <li>&bull; Prevent double booking</li>
                            <li>&bull; Auto confirmation after approval</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Damage and Penalty Logging</h3>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                            <li>&bull; Record broken or missing items</li>
                            <li>&bull; Auto compute penalty</li>
                            <li>&bull; Track unpaid penalties</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 sm:col-span-2">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Reports and Analytics</h3>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1 sm:grid sm:grid-cols-2">
                            <li>&bull; Most reserved facility</li>
                            <li>&bull; Peak reservation days</li>
                            <li>&bull; Total revenue from rentals</li>
                            <li>&bull; Damage frequency report</li>
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="t-card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Resident Access</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    You can view available facilities and submit reservation requests. You will receive an email once your reservation is approved.
                </p>
            </div>
        @endif

        {{-- Plan features --}}
        <div class="t-card p-6">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4">Plan Features</h2>
            <ul class="grid sm:grid-cols-2 gap-3 text-sm">
                <li class="flex items-center gap-2.5">
                    @if($tenantPlanModel?->allows('reports'))
                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <span class="text-slate-700 dark:text-slate-200">Reports &amp; analytics</span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </span>
                        <span class="text-slate-400 dark:text-slate-500">Reports (upgrade plan)</span>
                    @endif
                </li>
                <li class="flex items-center gap-2.5">
                    @if($tenantPlanModel?->allows('qr_checkin'))
                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <span class="text-slate-700 dark:text-slate-200">QR check-in on reservations</span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </span>
                        <span class="text-slate-400 dark:text-slate-500">QR check-in (Premium)</span>
                    @endif
                </li>
                <li class="flex items-center gap-2.5">
                    @if($tenantPlanModel?->allows('payments'))
                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <span class="text-slate-700 dark:text-slate-200">Payment-ready workflows</span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </span>
                        <span class="text-slate-400 dark:text-slate-500">Payments (Standard+)</span>
                    @endif
                </li>
            </ul>
        </div>

    </div>

</x-tenant-layout>
