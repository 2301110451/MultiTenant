<x-tenant-layout title="Support" breadcrumb="Support">
    <div class="px-6 py-8 sm:px-10 space-y-6" data-live-endpoint="{{ route('tenant.realtime.support') }}" data-live-interval="10000">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Support Tickets</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Open: <strong data-live-key="openCount">{{ $tickets->getCollection()->where('status', 'open')->count() }}</strong>
            <span class="ml-2">In progress: <strong data-live-key="inProgressCount">{{ $tickets->getCollection()->where('status', 'in_progress')->count() }}</strong></span>
            <span class="ml-2">Resolved/Closed: <strong data-live-key="resolvedCount">{{ $tickets->getCollection()->whereIn('status', ['resolved', 'closed'])->count() }}</strong></span>
        </p>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('tenant.support.store') }}" class="t-card p-6 space-y-4">
            @csrf
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Create Ticket</h2>
            <div>
                <label class="t-label">Subject</label>
                <input name="subject" class="t-input" required>
            </div>
            <div>
                <label class="t-label">Priority</label>
                <select name="priority" class="t-input">
                    <option>low</option><option selected>medium</option><option>high</option><option>urgent</option>
                </select>
            </div>
            <div>
                <label class="t-label">Description</label>
                <textarea name="description" rows="4" class="t-input" required></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="t-btn-primary">Submit Ticket</button>
            </div>
        </form>

        <div class="t-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <th class="text-left px-4 py-3">Subject</th>
                    <th class="text-left px-4 py-3">Priority</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Date</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($tickets as $ticket)
                    <tr>
                        <td class="px-4 py-3">{{ $ticket->subject }}</td>
                        <td class="px-4 py-3 capitalize">{{ $ticket->priority }}</td>
                        <td class="px-4 py-3 capitalize">{{ $ticket->status }}</td>
                        <td class="px-4 py-3">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No tickets yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $tickets->links() }}</div>
        </div>
    </div>
</x-tenant-layout>
