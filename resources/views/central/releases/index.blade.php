<x-central-layout title="Central Releases" breadcrumb="Central Releases">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Central Releases</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                Sync from GitHub, review detected updates, approve/reject, then log and publish a system version.
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        <div class="c-card p-5">
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Local Version</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $localLatestVersion ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">GitHub Version</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $githubLatestVersion ?? 'N/A' }}</p>
                    @if($githubLatestCommitSha)
                        <p class="mt-1 text-xs text-slate-500">Latest commit: {{ $githubLatestCommitSha }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Sync Status</p>
                    @if($githubLatestVersion === null && ! $githubLatestCommitSha)
                        <p class="mt-1 text-sm font-semibold text-slate-600">GitHub not available</p>
                    @elseif($hasNewGithubCommit)
                        <p class="mt-1 text-sm font-semibold text-emerald-600">New GitHub commit detected. Click Sync from GitHub.</p>
                    @elseif($hasNewGithubRelease)
                        <p class="mt-1 text-sm font-semibold text-emerald-600">New release update available</p>
                    @else
                        <p class="mt-1 text-sm font-semibold text-slate-600">No release update</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="c-card p-5 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('central.releases.detect-and-store') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
                        Sync from GitHub
                    </button>
                </form>
            </div>
        </div>

        <div class="c-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Detected Updates</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Version</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($releases as $release)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $release->title ?? 'GitHub update' }}</td>
                            <td class="px-4 py-3">{{ $release->version ?? $release->suggested_version ?? '—' }}</td>
                            <td class="px-4 py-3 uppercase">{{ $release->release_type ?? strtoupper((string) ($release->changes_detected[0] ?? 'feature')) }}</td>
                            <td class="px-4 py-3">{{ $release->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 capitalize">{{ $release->status }}</td>
                            <td class="px-4 py-3">
                                @if(in_array($release->status, ['detected', 'rejected'], true))
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('central.releases.approve', $release) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-500">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('central.releases.reject', $release) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-500">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif($release->status === 'approved')
                                    <a href="{{ route('central.releases.index', ['release' => $release->id]) }}" class="text-xs text-indigo-600 hover:text-indigo-500">
                                        Log System Version
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">Published</span>
                                @endif
                                @if($loop->first)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">
                                        Latest
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-slate-500">No updates detected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $releases->links() }}</div>
        </div>

        @if($selectedRelease)
            <div class="c-card p-5 space-y-4">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Log System Version</h2>
                <form method="POST" action="{{ route('central.releases.save-version', $selectedRelease) }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Version</label>
                        <input type="text" name="version" value="{{ old('version', $selectedRelease->suggested_version) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Type of feature</label>
                        <select name="release_type" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                            @php
                                $selectedType = strtolower((string) old('release_type', $selectedRelease->release_type ?: ($selectedRelease->changes_detected[0] ?? 'feature')));
                            @endphp
                            <option value="feature" @selected($selectedType === 'feature')>feature</option>
                            <option value="security" @selected($selectedType === 'security')>security</option>
                            <option value="maintenance" @selected($selectedType === 'maintenance' || $selectedType === 'fix' || $selectedType === 'breaking')>maintenance</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Notes</label>
                        <textarea name="notes" rows="4" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>{{ old('notes', $selectedRelease->notes) }}</textarea>
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="c-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Version History</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Version</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Released</th>
                        <th class="px-4 py-3 text-left">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($versions as $version)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $version->version }}</td>
                            <td class="px-4 py-3 uppercase">{{ $version->release_type }}</td>
                            <td class="px-4 py-3">{{ $version->released_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit((string) $version->notes, 120) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-slate-500">No system versions logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="c-card overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Release Audit Log</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Actor</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Record</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($releaseAudits as $audit)
                        @php
                            $recordText = (string) ($audit->version ?? ($audit->message ?? '—'));
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $audit->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $audit->scope ?? 'all_tenants' }}</td>
                            <td class="px-4 py-3">{{ $audit->actor?->name ?? 'System' }}</td>
                            <td class="px-4 py-3">{{ $audit->action }}</td>
                            <td class="px-4 py-3"><span>{{ $recordText }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-slate-500">No release audit logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-central-layout>
