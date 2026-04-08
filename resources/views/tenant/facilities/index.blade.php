@php $tb = \App\Support\TenantAppearance::theme()['button']; @endphp
<x-tenant-layout title="Facilities" breadcrumb="Facilities">
    @php $canManage = auth('tenant')->user()?->isSecretary() || auth('tenant')->user()?->isCaptain(); @endphp

    <div class="px-6 py-8 sm:px-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Facilities</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $canManage ? 'Manage halls, courts, and bookable spaces.' : 'Browse available halls, courts, and bookable spaces.' }}</p>
            </div>
            @if($canManage)
                <a href="{{ route('tenant.facilities.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm transition {{ $tb }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Add facility
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-5 text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="t-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Name</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Capacity</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Rate / hr</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Status</th>
                            @if($canManage)
                                <th class="px-6 py-3.5"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($facilities as $facility)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-slate-100">{{ $facility->name }}</td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ $facility->capacity }}</td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ number_format($facility->hourly_rate, 2) }}</td>
                                <td class="px-6 py-3.5">
                                    @if($facility->is_active)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-full px-2.5 py-0.5">Active</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full px-2.5 py-0.5">Inactive</span>
                                    @endif
                                </td>
                                @if($canManage)
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('tenant.facilities.edit', $facility) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Edit</a>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? '5' : '4' }}" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    @if($canManage)
                                        No facilities yet. <a href="{{ route('tenant.facilities.create') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Add one</a>.
                                    @else
                                        No active facilities are currently available.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">{{ $facilities->links() }}</div>
        </div>
    </div>

</x-tenant-layout>
