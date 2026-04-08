<x-central-layout title="Barangays" breadcrumb="Barangays">

    <div class="px-6 py-8 sm:px-10 space-y-6">

        {{-- header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Barangay Tenants</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Manage all registered barangays and their subscriptions.</p>
            </div>
            <a href="{{ route('central.tenants.create') }}"
               class="btn-primary shrink-0 px-5 py-2.5 font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Barangay
            </a>
        </div>

        {{-- flash messages --}}
        @if(session('warning'))
        <div class="flex items-start gap-2.5 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <span>{{ session('warning') }}</span>
        </div>
        @endif

        @if(session('mail_config_notice'))
        <div class="flex items-start gap-2.5 text-sm text-amber-900 bg-amber-50 border border-amber-300 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <span>{{ session('mail_config_notice') }}</span>
        </div>
        @endif

        @if(session('status') === 'tenant-updated' && ! session('success'))
        <div class="text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            Barangay settings saved.
        </div>
        @endif

        @if(session('success'))
        <div class="text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 space-y-2">
            <div class="flex items-start gap-2.5">
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

        {{-- table card --}}
        <div class="c-card overflow-hidden">

            @if($tenants->isEmpty())
                <div class="py-20 text-center">
                    <svg class="w-14 h-14 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                    <p class="text-slate-400 dark:text-slate-500 font-semibold">No barangays registered yet.</p>
                    <a href="{{ route('central.tenants.create') }}" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Register the first barangay</a>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700">
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Barangay Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Domain</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Plan</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Registered</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($tenants as $tenant)
                        @php
                            $planName   = $tenant->subscription?->plan?->name ?? 'Free';
                            $planColors = [
                                'basic'    => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                'standard' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                'premium'  => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            ];
                            $status = $tenant->status ?? 'active';
                        @endphp
                        <tr class="group transition-colors hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $tenant->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500">ID #{{ $tenant->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php $d = $tenant->domains->first()?->domain; @endphp
                                @if($d)
                                    <a href="{{ \App\Support\Tenancy::tenantPortalUrl($d) }}" target="_blank" rel="noopener noreferrer"
                                       class="font-mono text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg px-2.5 py-1 inline-flex items-center gap-1">
                                        {{ $d }}
                                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $planColors[strtolower($planName)] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                    {{ $planName }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-400 dark:text-slate-500 text-xs">{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <a href="{{ route('central.tenants.edit', $tenant) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('central.tenants.destroy', $tenant) }}"
                                          onsubmit="return confirm('Delete Barangay {{ addslashes($tenant->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($tenants, 'links') && $tenants->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $tenants->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>

</x-central-layout>
