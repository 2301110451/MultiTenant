<x-central-layout title="Deployment Candidates" breadcrumb="Deployment Candidates">
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
                Deployment control-plane tables are not ready yet. Run <code>php artisan migrate</code> to create the required tables, then refresh this page.
            </div>
        @endif

        <div class="c-card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Deployment Candidates</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        Each candidate requires explicit review before any deployment pipeline can be allowed.
                    </p>
                </div>
                <a href="{{ route('central.global-updates.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    Back to Global Updates
                </a>
            </div>
        </div>

        <div class="c-card p-4 sm:p-5">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Latest Added from GitHub</h2>
            @if(!empty($latestDetectedCommit))
                <p class="mt-1 text-xs text-slate-500 break-all">Commit/Tag: {{ $latestDetectedCommit }}</p>
            @endif
            @if(($latestDetectedFiles ?? collect())->isNotEmpty())
                <div class="mt-3 space-y-1">
                    @foreach($latestDetectedFiles->take(10) as $file)
                        <p class="text-xs text-slate-600 dark:text-slate-300 break-all">{{ $file }}</p>
                    @endforeach
                    @if($latestDetectedFiles->count() > 10)
                        <p class="text-xs text-slate-400">+{{ $latestDetectedFiles->count() - 10 }} more files</p>
                    @endif
                </div>
            @else
                <p class="mt-2 text-xs text-slate-500">No file list available yet. Add/commit a new GitHub file and refresh this page.</p>
            @endif
        </div>

        <div class="grid gap-3 sm:grid-cols-3" data-live-endpoint="{{ route('central.realtime.deployment-candidates') }}" data-live-interval="10000">
            <div class="c-card p-4">
                <p class="text-xs text-slate-500">Pending review</p>
                <p class="text-2xl font-bold text-amber-600" data-live-key="pendingReviewCount">{{ $candidates->where('status', 'pending_review')->count() }}</p>
            </div>
            <div class="c-card p-4">
                <p class="text-xs text-slate-500">Approved</p>
                <p class="text-2xl font-bold text-emerald-600" data-live-key="approvedCount">{{ $candidates->where('status', 'approved')->count() }}</p>
            </div>
            <div class="c-card p-4">
                <p class="text-xs text-slate-500">Rejected</p>
                <p class="text-2xl font-bold text-rose-600" data-live-key="rejectedCount">{{ $candidates->where('status', 'rejected')->count() }}</p>
            </div>
        </div>

        <div class="c-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Event</th>
                        <th class="px-4 py-3 text-left">Date/Time</th>
                        <th class="px-4 py-3 text-left">Risk</th>
                        <th class="px-4 py-3 text-left">Score</th>
                        <th class="px-4 py-3 text-left">Blast Radius</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($candidates as $candidate)
                        @php
                            $event = $candidate->updateEvent;
                            $riskClass = match ($candidate->risk_level) {
                                'high' => 'text-rose-700',
                                'medium' => 'text-amber-700',
                                default => 'text-emerald-700',
                            };
                            $normalized = is_array($event?->normalized) ? $event->normalized : [];
                            $changedFiles = collect($normalized['files'] ?? [])
                                ->map(static fn ($file) => trim((string) $file))
                                ->filter()
                                ->values();
                        @endphp
                        <tr>
                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ strtoupper($event?->event_type ?? 'unknown') }}
                                </div>
                                <div class="text-xs text-slate-500 mt-1 break-all">
                                    {{ $event?->commit_sha ?: ($event?->tag ?: 'n/a') }}
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $candidate->change_summary ?: 'No summary.' }}
                                </div>
                                @if($changedFiles->isNotEmpty())
                                    <div class="mt-2 space-y-1">
                                        <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Changed files</p>
                                        <div class="space-y-1">
                                            @foreach($changedFiles->take(5) as $file)
                                                <p class="text-[11px] text-slate-500 break-all">{{ $file }}</p>
                                            @endforeach
                                            @if($changedFiles->count() > 5)
                                                <p class="text-[11px] text-slate-400">+{{ $changedFiles->count() - 5 }} more files</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-slate-600 dark:text-slate-300">
                                {{ $event?->received_at?->format('M d, Y h:i A') ?? $candidate->created_at?->format('M d, Y h:i A') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 align-top {{ $riskClass }}">{{ strtoupper($candidate->risk_level) }}</td>
                            <td class="px-4 py-3 align-top">{{ $candidate->risk_score }}</td>
                            <td class="px-4 py-3 align-top">{{ $candidate->blast_radius ?? 'limited' }}</td>
                            <td class="px-4 py-3 align-top">{{ str_replace('_', ' ', $candidate->status) }}</td>
                            <td class="px-4 py-3 align-top">
                                @if($candidate->status === 'pending_review')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('central.global-updates.candidates.approve', $candidate) }}" class="flex gap-2">
                                            @csrf
                                            <input type="text" name="decision_note" class="px-2 py-1 rounded border border-slate-300 text-xs" placeholder="Optional approval note">
                                            <button class="px-3 py-1 rounded bg-emerald-600 text-white text-xs font-semibold">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('central.global-updates.candidates.reject', $candidate) }}" class="flex gap-2">
                                            @csrf
                                            <input type="text" name="decision_note" class="px-2 py-1 rounded border border-slate-300 text-xs" placeholder="Required rejection reason" required>
                                            <button class="px-3 py-1 rounded bg-rose-600 text-white text-xs font-semibold">Reject</button>
                                        </form>
                                    </div>
                                @elseif($candidate->status === 'approved')
                                    <form method="POST" action="{{ route('central.global-updates.candidates.validate', $candidate) }}" class="flex gap-2">
                                        @csrf
                                        <button class="px-3 py-1 rounded bg-indigo-600 text-white text-xs font-semibold">Queue validation</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Decision locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-slate-500" colspan="7">No deployment candidates yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $candidates->links() }}</div>
        </div>

        <div class="c-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Deployment Runs</h2>
                <span class="text-xs text-slate-500">Staging-first, production locked by default</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Run</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Environment</th>
                        <th class="px-4 py-3 text-left">Snapshot</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($runs as $run)
                        @php
                            $isDeployable = $run->status === 'validated';
                            $isAlreadyDeployed = in_array($run->status, ['deployed', 'deployed_dry_run'], true);
                        @endphp
                        <tr>
                            <td class="px-4 py-3">#{{ $run->id }}</td>
                            <td class="px-4 py-3">{{ $run->status }}</td>
                            <td class="px-4 py-3">{{ $run->environment }}</td>
                            <td class="px-4 py-3">{{ $run->snapshot?->version ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if(in_array($run->status, ['pending_validation', 'validation_queued'], true))
                                        <form method="POST" action="{{ route('central.global-updates.runs.mark-validated', $run) }}">
                                            @csrf
                                            <button class="px-3 py-1 rounded bg-indigo-600 text-white text-xs font-semibold">Mark validated</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('central.global-updates.runs.deploy', $run) }}">
                                        @csrf
                                        <button
                                            class="px-3 py-1 rounded text-xs font-semibold {{ $isDeployable ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600 cursor-not-allowed' }}"
                                            @if(! $isDeployable) disabled title="{{ $isAlreadyDeployed ? 'Already deployed' : 'Validate run first' }}" @endif
                                        >
                                            {{ $isAlreadyDeployed ? 'Deployed' : 'Deploy' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('central.global-updates.runs.undo', $run) }}" class="flex items-center gap-2">
                                        @csrf
                                        <select name="snapshot_id" class="px-2 py-1 rounded border border-slate-300 text-xs" required>
                                            <option value="">Snapshot</option>
                                            @foreach($snapshots as $snapshot)
                                                <option value="{{ $snapshot->id }}">{{ $snapshot->version }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="reason" class="px-2 py-1 rounded border border-slate-300 text-xs" placeholder="Undo reason" required maxlength="500">
                                        <button class="px-3 py-1 rounded bg-rose-600 text-white text-xs font-semibold">UNDO</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-slate-500" colspan="5">No deployment runs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-central-layout>
