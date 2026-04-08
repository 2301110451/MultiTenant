<x-central-layout title="Dashboard" breadcrumb="Dashboard">

    <div class="px-6 py-8 sm:px-10 space-y-8">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">System-wide overview &mdash; {{ now()->format('l, F j, Y') }}</p>
            </div>
            <a href="{{ route('central.tenants.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Barangay
            </a>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            @php
            $stats = [
                ['label'=>'Total Barangays','value'=>$tenantCount,'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5','bg'=>'bg-indigo-50 dark:bg-indigo-900/20','icon_bg'=>'bg-indigo-600','text'=>'text-indigo-600 dark:text-indigo-400','border'=>'border-indigo-100 dark:border-indigo-800'],
                ['label'=>'Active','value'=>$activeTenants,'icon'=>'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z','bg'=>'bg-emerald-50 dark:bg-emerald-900/20','icon_bg'=>'bg-emerald-500','text'=>'text-emerald-600 dark:text-emerald-400','border'=>'border-emerald-100 dark:border-emerald-800'],
                ['label'=>'Suspended','value'=>$suspendedTenants,'icon'=>'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z','bg'=>'bg-red-50 dark:bg-red-900/20','icon_bg'=>'bg-red-500','text'=>'text-red-600 dark:text-red-400','border'=>'border-red-100 dark:border-red-800'],
                ['label'=>'Subscription Plans','value'=>$totalPlans,'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01','bg'=>'bg-amber-50 dark:bg-amber-900/20','icon_bg'=>'bg-amber-500','text'=>'text-amber-600 dark:text-amber-400','border'=>'border-amber-100 dark:border-amber-800'],
            ];
            @endphp

            @foreach($stats as $stat)
            <div class="bg-white dark:bg-slate-900 border {{ $stat['border'] }} rounded-2xl p-5 card-shadow flex items-start gap-4 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl {{ $stat['icon_bg'] }} flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-extrabold {{ $stat['text'] }} mt-0.5">{{ $stat['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Recent tenants table --}}
        <div class="c-card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Recently Added Barangays</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Latest 8 tenant registrations</p>
                </div>
                <a href="{{ route('central.tenants.index') }}"
                   class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1">
                    View all
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($recentTenants->isEmpty())
                <div class="py-16 text-center">
                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                    <p class="text-slate-400 dark:text-slate-500 text-sm font-medium">No barangays registered yet.</p>
                    <a href="{{ route('central.tenants.create') }}" class="mt-2 inline-block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Add the first barangay</a>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Barangay</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Domain</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Plan</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Status</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Registered</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($recentTenants as $tenant)
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors group">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs">
                                {{ $tenant->domains->first()?->domain ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @php
                                    $planName = $tenant->subscription?->plan?->name ?? 'Free';
                                    $planColors = [
                                        'basic'    => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                        'standard' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                        'premium'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $planColors[strtolower($planName)] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                @if(($tenant->status ?? 'active') === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-400 dark:text-slate-500 text-xs">{{ $tenant->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('central.tenants.edit', $tenant) }}"
                                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-semibold opacity-0 group-hover:opacity-100 transition">
                                    Edit &rarr;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

</x-central-layout>
