<x-central-layout title="Subscription requests" breadcrumb="Subscription requests">

    <div class="px-6 py-8 sm:px-10 space-y-6 page-fade">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Subscription requests</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    Unsubscribe and extension submissions from suspended barangay portals.
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $pendingCount }} pending</span>
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ session('warning') }}
            </div>
        @endif
        @if (session('mail_config_notice'))
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                {{ session('mail_config_notice') }}
            </div>
        @endif

        <div class="c-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3">Barangay</th>
                            <th class="px-4 py-3">Intent</th>
                            <th class="px-4 py-3 max-w-xs">Message</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Review</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($intents as $row)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $row->created_at->timezone(config('app.timezone'))->format('M j, Y g:i a') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ $row->tenant?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row->intent_type === 'extend')
                                        <span class="inline-flex rounded-lg bg-sky-100 dark:bg-sky-900/30 px-2 py-0.5 text-xs font-medium text-sky-800 dark:text-sky-300">Extension</span>
                                    @else
                                        <span class="inline-flex rounded-lg bg-slate-200 dark:bg-slate-700 px-2 py-0.5 text-xs font-medium text-slate-800 dark:text-slate-300">Unsubscribe</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $row->message }}">
                                    {{ $row->message ? \Illuminate\Support\Str::limit($row->message, 80) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row->status === 'pending')
                                        <span class="inline-flex text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-full px-2.5 py-0.5">Pending</span>
                                    @elseif ($row->status === 'approved')
                                        <span class="inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-full px-2.5 py-0.5">Approved</span>
                                    @else
                                        <span class="inline-flex text-xs font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-full px-2.5 py-0.5">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                    @if ($row->reviewed_at)
                                        {{ $row->reviewed_at->timezone(config('app.timezone'))->format('M j, Y') }}
                                        @if ($row->reviewer)
                                            <br><span class="text-slate-400 dark:text-slate-500">{{ $row->reviewer->email }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($row->isPending())
                                        <div class="flex flex-col items-end gap-2 sm:flex-row sm:justify-end">
                                            <form method="post" action="{{ route('central.subscription-intents.approve', $row) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white transition">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="post" action="{{ route('central.subscription-intents.reject', $row) }}" class="inline" onsubmit="return confirm('Reject this request?');">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">No requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($intents->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $intents->links() }}
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
            <strong class="text-slate-800 dark:text-slate-200">Approve:</strong> extension requests reactivate a suspended portal and email officers (when SMTP is configured).
            Unsubscribe approvals only mark the request as accepted; remove the tenant from Barangays if you are closing the account.
            <strong class="text-slate-800 dark:text-slate-200 ml-2">Reject:</strong> leaves the portal as-is (usually still suspended).
        </div>
    </div>
</x-central-layout>
