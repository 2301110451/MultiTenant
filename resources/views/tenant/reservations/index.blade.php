@php $tb = \App\Support\TenantAppearance::theme()['button']; @endphp
<x-tenant-layout title="Reservations" breadcrumb="Reservations">

    <div class="px-6 py-8 sm:px-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reservations</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track and manage booking requests.</p>
            </div>
            <a href="{{ route('tenant.reservations.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm transition {{ $tb }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New reservation
            </a>
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
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Facility</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">When</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Requested by</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($reservations as $reservation)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-slate-100">{{ $reservation->facility->name }}</td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">
                                    {{ $reservation->starts_at->format('M d, H:i') }} &mdash; {{ $reservation->ends_at->format('H:i') }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ $reservation->user->name }}</td>
                                <td class="px-6 py-3.5">
                                    @php
                                        $statusColors = [
                                            'pending'   => 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700',
                                            'approved'  => 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700',
                                            'rejected'  => 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700',
                                            'completed' => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                        ];
                                        $sc = $statusColors[$reservation->status->value] ?? 'text-slate-600 bg-slate-100 border-slate-200 dark:text-slate-400 dark:bg-slate-800 dark:border-slate-700';
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold border rounded-full px-2.5 py-0.5 capitalize {{ $sc }}">
                                        {{ $reservation->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('tenant.reservations.show', $reservation) }}"
                                       class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    No reservations yet. <a href="{{ route('tenant.reservations.create') }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">{{ $reservations->links() }}</div>
        </div>
    </div>

</x-tenant-layout>
