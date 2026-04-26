<x-central-layout title="Support Tickets" breadcrumb="Support Tickets">
    @php
        $priorityClasses = [
            'low' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/35 dark:text-emerald-300 dark:border-emerald-800',
            'medium' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/35 dark:text-blue-300 dark:border-blue-800',
            'high' => 'bg-amber-100 text-amber-900 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800',
            'urgent' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-200 dark:border-red-800',
        ];
        $statusClasses = [
            'open' => 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600',
            'in_progress' => 'bg-orange-100 text-orange-900 border-orange-200 dark:bg-orange-900/40 dark:text-orange-200 dark:border-orange-800',
            'resolved' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/35 dark:text-green-300 dark:border-green-800',
            'closed' => 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600',
        ];
    @endphp

    <div
        class="px-6 py-8 sm:px-10 space-y-6"
        data-live-endpoint="{{ route('central.realtime.support-tickets') }}"
        data-live-interval="10000"
        x-data="{
            show: false,
            selected: null,
            open(t) {
                this.selected = t;
                this.show = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.show = false;
                this.selected = null;
                document.body.classList.remove('overflow-hidden');
            },
        }"
        @keydown.escape.window="close()"
    >
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Support Tickets</h1>
        <p class="mt-1.5 text-base font-semibold text-slate-600 dark:text-slate-300">
            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 dark:bg-sky-900/30 px-2.5 py-1 text-sky-800 dark:text-sky-300">
                Open: <strong data-live-key="openCount">{{ $tickets->getCollection()->where('status', 'open')->count() }}</strong>
            </span>
            <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-orange-100 dark:bg-orange-900/30 px-2.5 py-1 text-orange-800 dark:text-orange-300">
                In progress: <strong data-live-key="inProgressCount">{{ $tickets->getCollection()->where('status', 'in_progress')->count() }}</strong>
            </span>
            <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2.5 py-1 text-emerald-800 dark:text-emerald-300">
                Resolved/Closed: <strong data-live-key="resolvedCount">{{ $tickets->getCollection()->whereIn('status', ['resolved', 'closed'])->count() }}</strong>
            </span>
        </p>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="c-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Recent Release Activities</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <th class="px-4 py-2.5 text-left">Date</th>
                    <th class="px-4 py-2.5 text-left">Actor</th>
                    <th class="px-4 py-2.5 text-left">Action</th>
                    <th class="px-4 py-2.5 text-left">Record</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($releaseActivities as $activity)
                    <tr>
                        <td class="px-4 py-2.5">{{ $activity->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-2.5">{{ $activity->actor?->name ?? 'System' }}</td>
                        <td class="px-4 py-2.5">{{ $activity->action }}</td>
                        <td class="px-4 py-2.5">{{ $activity->version ?? $activity->message }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2.5 text-slate-500">No release activity yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="c-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($tickets as $ticket)
                        @php
                            $payload = [
                                'id' => $ticket->id,
                                'subject' => $ticket->subject,
                                'priority' => $ticket->priority,
                                'description' => $ticket->description,
                                'status' => $ticket->status,
                                'tenant_name' => $ticket->tenant?->name ?? '—',
                                'requester_name' => $ticket->requester_name,
                                'requester_email' => $ticket->requester_email,
                            ];
                            $pClass = $priorityClasses[strtolower((string) $ticket->priority)] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            $sClass = $statusClasses[$ticket->status] ?? 'bg-slate-100 text-slate-700';
                        @endphp
                        <tr
                            class="cursor-pointer hover:bg-slate-50/90 dark:hover:bg-slate-800/60 transition-colors"
                            @click='open(@json($payload))'
                        >
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $ticket->tenant?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ \Illuminate\Support\Str::limit($ticket->subject, 60) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize {{ $pClass }}">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize {{ $sClass }}">
                                    {{ str_replace('_', ' ', $ticket->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    @click.stop='open(@json($payload))'
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $tickets->links() }}</div>
        </div>

        {{-- Modal --}}
        <div
            x-show="show"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close()"></div>
            <div
                class="relative z-10 w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900"
                @click.stop
                x-show="show && selected"
            >
                <div class="flex items-start justify-between gap-4 mb-4">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Ticket details</h2>
                    <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="close()" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4" x-show="selected" x-transition>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tenant</p>
                            <p class="mt-1 text-sm text-slate-800 dark:text-slate-200" x-text="selected.tenant_name"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Subject</p>
                            <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100" x-text="selected.subject"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Current status</p>
                            <p class="mt-1">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600': selected.status === 'open',
                                        'bg-orange-100 text-orange-900 border-orange-200 dark:bg-orange-900/40 dark:text-orange-200 dark:border-orange-800': selected.status === 'in_progress',
                                        'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/35 dark:text-green-300 dark:border-green-800': selected.status === 'resolved',
                                        'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600': selected.status === 'closed',
                                    }"
                                    x-text="selected.status.split('_').join(' ')"
                                ></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Priority</p>
                            <p class="mt-1">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/35 dark:text-emerald-300 dark:border-emerald-800': selected.priority === 'low',
                                        'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/35 dark:text-blue-300 dark:border-blue-800': selected.priority === 'medium',
                                        'bg-amber-100 text-amber-900 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800': selected.priority === 'high',
                                        'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-200 dark:border-red-800': selected.priority === 'urgent',
                                    }"
                                    x-text="selected.priority"
                                ></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Description</p>
                            <div class="mt-1 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700 whitespace-pre-wrap dark:border-slate-600 dark:bg-slate-800/50 dark:text-slate-300" x-text="selected.description"></div>
                        </div>
                        <div class="border-t border-slate-100 pt-4 dark:border-slate-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">Update status</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">The reporter will receive an email when the status changes.</p>
                            <form
                                method="POST"
                                :action="`{{ url('/central/support-tickets') }}/${selected.id}`"
                            >
                                @csrf
                                @method('PUT')
                                <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                                    <div class="flex-1">
                                        <label class="sr-only" for="modal-status">Status</label>
                                        <select
                                            id="modal-status"
                                            name="status"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                            x-model="selected.status"
                                        >
                                            @foreach(['open','in_progress','resolved','closed'] as $st)
                                                <option value="{{ $st }}">{{ str_replace('_', ' ', $st) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                                        Save update
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</x-central-layout>
