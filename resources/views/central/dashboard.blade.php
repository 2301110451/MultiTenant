<x-central-layout title="Dashboard" breadcrumb="Dashboard">

    @php
        $openCreateModal = $errors->any() && $editTenantPayload === null && (
            $errors->has('name') || $errors->has('plan_id') || $errors->has('provisioning')
            || $errors->has('tenant_admin_email') || $errors->has('tenant_admin_password')
            || $errors->has('staff_email') || $errors->has('staff_password')
        );
        $editDefaultForAlpine = $editTenantPayload ?? ['id' => null, 'name' => '', 'domain' => '', 'plan_id' => '', 'status' => 'active'];
    @endphp

    <div
        class="px-6 py-8 sm:px-10 space-y-8"
        data-live-endpoint="{{ route('central.realtime.dashboard') }}"
        data-live-interval="15000"
        x-data="{
            showCreateModal: @json($openCreateModal),
            showEditModal: @json($editTenantPayload !== null),
            edit: @js($editDefaultForAlpine),
            openEditModal(row) {
                this.edit = {
                    id: row.id,
                    name: row.name,
                    domain: row.domain,
                    plan_id: row.plan_id === null || row.plan_id === undefined ? '' : row.plan_id,
                    status: row.status || 'active',
                };
                this.showEditModal = true;
            },
            closeEditModal() {
                this.showEditModal = false;
                this.edit = { id: null, name: '', domain: '', plan_id: '', status: 'active' };
            }
        }"
    >

        {{-- Page header --}}
        <div class="slide-up">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">System-wide overview &mdash; {{ now()->format('l, F j, Y') }}</p>
        </div>

        @if(session('mail_config_notice'))
        <div class="alert-warning">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <span>{{ session('mail_config_notice') }}</span>
        </div>
        @endif

        @if(session('status') === 'tenant-updated' && ! session('success'))
        <div class="alert-success">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Barangay settings saved.</span>
        </div>
        @endif

        @if(session('success'))
        <div class="alert-success space-y-2">
            <div class="flex items-start gap-3">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @if(session('portal_url'))
                <a href="{{ session('portal_url') }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    Open barangay portal
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            @endif
        </div>
        @endif

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            @php
            $stats = [
                ['label'=>'Total Barangays','value'=>$tenantCount,'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5','color'=>'indigo','liveKey'=>'tenantCount'],
                ['label'=>'Active','value'=>$activeTenants,'icon'=>'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'emerald','liveKey'=>'activeTenants'],
                ['label'=>'Suspended','value'=>$suspendedTenants,'icon'=>'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z','color'=>'red','liveKey'=>'suspendedTenants'],
                ['label'=>'Subscribed','value'=>$subscribedTenants,'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01','color'=>'amber','liveKey'=>'subscribedTenants'],
            ];
            $colorMap = [
                'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'icon' => 'bg-gradient-to-br from-indigo-500 to-indigo-600', 'text' => 'text-indigo-600 dark:text-indigo-400', 'ring' => 'ring-indigo-100 dark:ring-indigo-800/30'],
                'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'icon' => 'bg-gradient-to-br from-emerald-500 to-emerald-600', 'text' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-100 dark:ring-emerald-800/30'],
                'red' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'icon' => 'bg-gradient-to-br from-red-500 to-red-600', 'text' => 'text-red-600 dark:text-red-400', 'ring' => 'ring-red-100 dark:ring-red-800/30'],
                'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'icon' => 'bg-gradient-to-br from-amber-500 to-amber-600', 'text' => 'text-amber-600 dark:text-amber-400', 'ring' => 'ring-amber-100 dark:ring-amber-800/30'],
            ];
            @endphp

            @foreach($stats as $i => $stat)
            @php $c = $colorMap[$stat['color']]; @endphp
            <div class="stat-card stat-card-{{ $stat['color'] === 'indigo' ? 'blue' : $stat['color'] }} group slide-up slide-up-delay-{{ $i + 1 }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                        <p class="text-3xl font-extrabold {{ $c['text'] }} mt-2 tabular-nums" data-live-key="{{ $stat['liveKey'] }}">{{ $stat['value'] }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl {{ $c['icon'] }} flex items-center justify-center shrink-0 shadow-sm ring-1 ring-white/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Recent tenants table --}}
        <div class="c-card overflow-hidden slide-up slide-up-delay-4">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Recently Added Barangays</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest 8 tenant registrations</p>
                </div>
                <a href="{{ route('central.tenants.index') }}"
                   class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                    View all
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($recentTenants->isEmpty())
                <div class="py-20 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                    </div>
                    <p class="text-slate-400 dark:text-slate-500 text-sm font-medium">No barangays registered yet.</p>
                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-semibold"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add the first barangay
                    </button>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-700">
                            <th class="text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-6 py-3.5">Barangay</th>
                            <th class="text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-6 py-3.5">Domain</th>
                            <th class="text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-6 py-3.5">Plan</th>
                            <th class="text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-6 py-3.5">Status</th>
                            <th class="text-left text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider px-6 py-3.5">Registered</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentTenants as $tenant)
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-xs shadow-sm ring-1 ring-white/20">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono text-xs">
                                {{ $tenant->domains->first()?->domain ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $planName = $tenant->subscription?->plan?->name ?? 'Free';
                                    $planColors = [
                                        'basic'    => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                        'standard' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                        'premium'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $planColors[strtolower($planName)] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if(($tenant->status ?? 'active') === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-glow"></span>Active
                                    </span>
                                @elseif(($tenant->status ?? 'active') === 'unsubscribed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>Unsubscribed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-400 dark:text-slate-500 text-xs">{{ $tenant->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $editRow = [
                                        'id' => $tenant->id,
                                        'name' => $tenant->name,
                                        'domain' => $tenant->domains->first()?->domain ?? '',
                                        'plan_id' => $tenant->plan_id,
                                        'status' => $tenant->status ?? 'active',
                                    ];
                                @endphp
                                <button
                                    type="button"
                                    @click="openEditModal(@js($editRow))"
                                    class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-semibold px-2.5 py-1 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                >
                                    Edit
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    {{-- Create barangay modal --}}
    <div
        x-show="showCreateModal"
        x-cloak
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-overlay"
        @keydown.escape.window="showCreateModal = false"
        @click.self="showCreateModal = false"
    >
        <div
            x-show="showCreateModal"
            @click.stop
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="modal-panel w-full max-w-3xl max-h-[min(92vh,900px)] overflow-y-auto p-6 sm:p-8 text-left"
        >
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Register New Barangay</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        A tenant domain and separate database are created automatically from the name.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    @click="showCreateModal = false"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @include('central.tenants._form', ['modal' => true, 'plans' => $plans])
        </div>
    </div>

    {{-- Edit barangay modal --}}
    <div
        x-show="showEditModal"
        x-cloak
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-overlay"
        @keydown.escape.window="closeEditModal()"
        @click.self="closeEditModal()"
    >
        <div
            x-show="showEditModal"
            @click.stop
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="modal-panel w-full max-w-2xl max-h-[min(92vh,900px)] overflow-y-auto p-6 sm:p-8 text-left"
        >
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit Barangay</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Update name, domain, plan, and status. Changes apply immediately.
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    @click="closeEditModal()"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @include('central.tenants._edit_modal_form', ['plans' => $plans, 'redirectTo' => 'dashboard'])
        </div>
    </div>

    </div>

</x-central-layout>
