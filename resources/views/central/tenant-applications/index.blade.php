<x-central-layout title="Tenant Applications" breadcrumb="Tenant Applications">
    <div class="px-6 py-8 sm:px-10 space-y-6 page-fade">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tenant Applications</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Review pending tenant requests and approve or reject them.</p>
            </div>
            <div class="text-sm font-medium text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 px-3 py-2 rounded-lg">
                {{ $pendingCount }} pending
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('warning') }}</div>
        @endif

        <div class="c-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3">Barangay</th>
                            <th class="px-4 py-3">Tenant Admin</th>
                            <th class="px-4 py-3">Staff</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($applications as $row)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $row->created_at->format('M j, Y g:i a') }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ $row->barangay_name }}
                                    @if($row->notes)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ \Illuminate\Support\Str::limit($row->notes, 70) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300 break-all max-w-[240px]">{{ $row->tenant_admin_email }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300 break-all max-w-[240px]">{{ $row->staff_email ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $row->plan?->name ?? 'No plan (Free)' }}</td>
                                <td class="px-4 py-3">
                                    @if($row->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-full px-2.5 py-0.5">Pending</span>
                                    @elseif($row->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-full px-2.5 py-0.5">Approved</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-full px-2.5 py-0.5">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($row->status === 'pending')
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('central.tenant-applications.approve', $row) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white transition">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('central.tenant-applications.reject', $row) }}" onsubmit="return confirm('Reject this tenant application?');">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Not approved by super admin.">
                                                <button type="submit" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500">Reviewed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">No tenant applications yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($applications->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">{{ $applications->links() }}</div>
            @endif
        </div>
    </div>
</x-central-layout>
