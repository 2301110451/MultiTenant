<x-central-layout title="Audit Logs" breadcrumb="Audit Logs">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Super Admin Audit Logs</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Track super admin actions in one table: date, tenant, actor, action, and record.
        </p>

        @if(! $tableReady)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                Audit log table is not ready. Run <code>php artisan migrate</code>.
            </div>
        @endif

        <form method="GET" action="{{ route('central.audit-logs.index') }}" class="c-card p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="date_from" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">From</label>
                <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            </div>
            <div>
                <label for="date_to" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">To</label>
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            </div>
            <div class="md:col-span-2 lg:col-span-5 flex items-center justify-end gap-2">
                <a href="{{ route('central.audit-logs.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    Reset
                </a>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
                    Apply Filters
                </button>
            </div>
        </form>

        <div class="c-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Actor</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Record</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        @php
                            $record = $log->version
                                ?: (is_array($log->metadata ?? null) ? (string) ($log->metadata['record'] ?? '') : '')
                                ?: (string) ($log->message ?? '—');
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">
                                {{ $log->created_at?->timezone($timezoneLabel)->format('M d, Y h:i:s A') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">
                                {{ $log->actor?->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($record, 150) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-slate-500 dark:text-slate-400" colspan="4">No audit logs found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-central-layout>
