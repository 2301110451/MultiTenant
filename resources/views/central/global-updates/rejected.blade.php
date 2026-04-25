<x-central-layout title="Rejected Deployment Candidates" breadcrumb="Rejected Deployment Candidates">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if(!empty($missingControlPlaneTables))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Deployment control-plane tables are not ready yet. Run <code>php artisan migrate</code> first.
            </div>
        @endif

        <div class="c-card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Rejected Deployment Candidates</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        Rejected candidates are separated from the main queue. You can restore them to deployment candidates.
                    </p>
                </div>
                <a href="{{ route('central.global-updates.candidates.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    Back to Deployment Candidates
                </a>
            </div>
        </div>

        <div class="c-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Event</th>
                        <th class="px-4 py-3 text-left">Date/Time</th>
                        <th class="px-4 py-3 text-left">Risk</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($candidates as $candidate)
                        @php $event = $candidate->updateEvent; @endphp
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ strtoupper($event?->event_type ?? 'unknown') }}</div>
                                <div class="text-xs text-slate-500 mt-1 break-all">{{ $event?->commit_sha ?: ($event?->tag ?: 'n/a') }}</div>
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600 dark:text-slate-300">
                                {{ $event?->received_at?->format('M d, Y h:i A') ?? $candidate->rejected_at?->format('M d, Y h:i A') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 align-top">{{ strtoupper((string) $candidate->risk_level) }}</td>
                            <td class="px-4 py-3 align-top">{{ str_replace('_', ' ', (string) $candidate->status) }}</td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('central.global-updates.candidates.restore', $candidate) }}">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-indigo-600 text-white text-xs font-semibold">Restore to candidates</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-slate-500" colspan="5">No rejected candidates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $candidates->links() }}</div>
        </div>
    </div>
</x-central-layout>
