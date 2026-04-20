@php
    $theme = \App\Support\TenantAppearance::theme(auth('tenant')->user());
    $tenantPlanModel = $tenant?->subscription?->plan ?? $tenant?->plan;
@endphp
<x-tenant-layout title="Dashboard" breadcrumb="Dashboard">

    <div class="px-6 py-8 sm:px-10 space-y-8" data-live-endpoint="{{ route('tenant.realtime.dashboard') }}" data-live-interval="12000">

        {{-- Page heading --}}
        <div class="slide-up">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Welcome back, <strong class="text-slate-700 dark:text-slate-200">{{ $user->name }}</strong>
                <span class="text-slate-300 dark:text-slate-600 mx-1">&middot;</span>
                <span class="capitalize">{{ str_replace('_', ' ', $user->role->value) }}</span>
            </p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="t-card t-card-hover p-5 slide-up slide-up-delay-1">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pending Reservations</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mt-2 tabular-nums" data-live-key="pendingApprovals">{{ $pendingApprovals }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center shadow-sm ring-1 ring-amber-400/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card t-card-hover p-5 slide-up slide-up-delay-2">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Facilities</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-100 mt-2 tabular-nums" data-live-key="facilitiesCount">{{ $facilitiesCount }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center shadow-sm ring-1 ring-emerald-400/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card t-card-hover p-5 slide-up slide-up-delay-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subscription</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100 mt-2">{{ $tenant?->subscription?->plan?->name ?? $tenant?->plan?->name ?? '—' }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-violet-400 to-violet-500 flex items-center justify-center shadow-sm ring-1 ring-violet-400/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="t-card t-card-hover p-5 ring-1 {{ $theme['panelRing'] }} slide-up slide-up-delay-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Plan Tier</p>
                        <p class="text-sm font-semibold mt-2 {{ $theme['breadcrumbAccent'] }}">{{ $theme['label'] }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-500 mt-1">UI accent matches plan.</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl {{ $theme['brandIcon'] }} flex items-center justify-center ring-1 ring-white/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        @if($user->canManageTenant())
            <div class="t-card p-6 slide-up">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Core Functions</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Management modules for Tenant Admin and Staff.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach([
                        ['Facility Management', ['Add facilities', 'Set maximum capacity', 'Define reservation rules', 'Set operating hours'], 'from-blue-500 to-blue-600'],
                        ['Equipment Tracking', ['Add items and quantity', 'Track condition', 'Log borrower', 'Set penalty fees'], 'from-teal-500 to-teal-600'],
                        ['Reservation System', ['Real time calendar', 'Prevent double booking', 'Auto confirmation after approval'], 'from-violet-500 to-violet-600'],
                        ['Damage and Penalty Logging', ['Record broken or missing items', 'Auto compute penalty', 'Track unpaid penalties'], 'from-rose-500 to-rose-600'],
                    ] as [$title, $items, $gradient])
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 hover:border-slate-300 dark:hover:border-slate-600 transition-colors group">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-2 h-2 rounded-full bg-gradient-to-r {{ $gradient }}"></div>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $title }}</h3>
                        </div>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1.5 pl-5">
                            @foreach($items as $item)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700/60 p-5 sm:col-span-2 hover:border-slate-300 dark:hover:border-slate-600 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-2 h-2 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600"></div>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100 text-sm">Reports and Analytics</h3>
                        </div>
                        <ul class="text-sm text-slate-600 dark:text-slate-300 space-y-1.5 sm:grid sm:grid-cols-2 sm:gap-x-6 pl-5">
                            @foreach(['Most reserved facility', 'Peak reservation days', 'Total revenue from rentals', 'Damage frequency report'] as $item)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="t-card p-6 slide-up">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-2">Resident Access</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    You can view available facilities and submit reservation requests. You will receive an email once your reservation is approved.
                </p>
            </div>
        @endif

        <div class="t-card p-6 slide-up">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Latest Barangay Announcements</h2>
                @if($user->hasPermission('updates.view'))
                    <a href="{{ route('tenant.updates.index') }}" class="text-xs font-semibold {{ $theme['breadcrumbAccent'] }}">View all</a>
                @endif
            </div>
            <div class="space-y-3">
                @forelse($recentTenantAnnouncements as $announcement)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="font-semibold text-sm text-slate-900 dark:text-slate-100">{{ $announcement->title }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $announcement->published_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 mt-2">{{ $announcement->message }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No barangay announcements yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Plan features --}}
        <div class="t-card p-6 slide-up">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-4">Plan Features</h2>
            <ul class="grid sm:grid-cols-2 gap-3 text-sm">
                @foreach([
                    ['reports', 'Reports & analytics', 'Reports (upgrade plan)'],
                    ['qr_checkin', 'QR check-in on reservations', 'QR check-in (Premium)'],
                    ['payments', 'Payment-ready workflows', 'Payments (Standard+)'],
                ] as [$feature, $enabledLabel, $disabledLabel])
                <li class="flex items-center gap-3">
                    @if($tenantPlanModel?->allows($feature))
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <span class="text-slate-700 dark:text-slate-200">{{ $enabledLabel }}</span>
                    @else
                        <span class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </span>
                        <span class="text-slate-400 dark:text-slate-500">{{ $disabledLabel }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

    </div>

</x-tenant-layout>
