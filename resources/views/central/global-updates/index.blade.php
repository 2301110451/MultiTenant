<x-central-layout title="Global Updates" breadcrumb="Global Updates">
    <div class="px-6 py-8 sm:px-10 space-y-6" x-data="{ showPublish: {{ $errors->any() ? 'true' : 'false' }}, globalScope: '{{ old('scope', 'all_tenants') }}' }">
        @php
            $githubToken = trim((string) config('services.github.token'));
            $githubOwner = trim((string) config('services.github.owner'));
            $githubRepo = trim((string) config('services.github.repo'));
            $githubConfigured = $githubToken !== '' && $githubOwner !== '' && $githubRepo !== '';
            $githubBaseUrl = ($githubOwner !== '' && $githubRepo !== '')
                ? "https://github.com/{$githubOwner}/{$githubRepo}"
                : null;
        @endphp

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

        <div class="c-card p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Global Updates</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        Publish one update for all tenants and resident portals. Sync from GitHub to import releases into this list.
                    </p>
                    @if($githubBaseUrl)
                        <div class="mt-2 flex items-center gap-3 text-xs">
                            <a href="{{ $githubBaseUrl }}/releases" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Open GitHub Releases</a>
                            <a href="{{ $githubBaseUrl }}/tags" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">Open GitHub Tags</a>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('central.global-updates.candidates.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                        Review candidates
                    </a>
                    @if($githubConfigured)
                        <form method="POST" action="{{ route('central.global-updates.sync') }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                                Sync from GitHub
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="px-3 py-2 rounded-xl border border-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed dark:border-slate-700 dark:text-slate-500">
                            Sync from GitHub
                        </button>
                    @endif
                    <button
                        type="button"
                        @click="showPublish = !showPublish"
                        @if(! $githubConfigured) disabled @endif
                        class="px-3 py-2 rounded-xl text-xs font-semibold {{ $githubConfigured ? 'bg-indigo-600 text-white hover:bg-indigo-500' : 'bg-indigo-300 text-white cursor-not-allowed dark:bg-indigo-900/40 dark:text-slate-300' }}"
                    >
                        Publish global update
                    </button>
                </div>
            </div>
        </div>

        @if(! $githubConfigured)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                GitHub integration is not configured. Set <code>GITHUB_TOKEN</code>, <code>GITHUB_OWNER</code>, and <code>GITHUB_REPO</code> in your runtime environment, then refresh this page.
            </div>
        @endif

        <div class="c-card p-6 space-y-4" x-show="showPublish" x-transition style="display: none;">
            <form method="POST" action="{{ route('central.global-updates.publish') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
                    <input id="title" name="title" value="{{ old('title') }}" class="mt-1 w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" required maxlength="255">
                    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" required maxlength="8000">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="update_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
                    <select id="update_type" name="update_type" class="mt-1 w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" required>
                        <option value="feature" @selected(old('update_type', 'feature') === 'feature')>feature</option>
                        <option value="security" @selected(old('update_type') === 'security')>security</option>
                        <option value="maintenance" @selected(old('update_type') === 'maintenance')>maintenance</option>
                    </select>
                    @error('update_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="scope" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Notification Scope</label>
                    <select id="scope" name="scope" x-model="globalScope" class="mt-1 w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300">
                        <option value="all_tenants" @selected(old('scope', 'all_tenants') === 'all_tenants')>All barangays/tenants</option>
                        <option value="selected" @selected(old('scope') === 'selected')>Selected tenants only</option>
                    </select>
                </div>

                <div>
                    <label for="selected_tenant_ids" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Selected tenants (optional)</label>
                    <select id="selected_tenant_ids" name="selected_tenant_ids[]" :disabled="globalScope !== 'selected'" class="mt-1 w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 disabled:bg-slate-100 disabled:text-slate-500 dark:disabled:bg-slate-800">
                        <option value="">All barangays/tenants</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(collect(old('selected_tenant_ids', []))->contains((string) $tenant->id) || collect(old('selected_tenant_ids', []))->contains($tenant->id))>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('selected_tenant_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('selected_tenant_ids.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end">
                    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Publish Global Update</button>
                </div>
            </form>
        </div>

        <div class="c-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Version</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">GitHub</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($updates as $update)
                        <tr>
                            <td class="px-4 py-3">{{ $update->title }}</td>
                            <td class="px-4 py-3">{{ $update->version ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $update->update_type ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $update->published_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($githubBaseUrl && ($update->github_tag_name || $update->version))
                                    @php
                                        $tagName = (string) ($update->github_tag_name ?: $update->version);
                                        $tagUrl = $githubBaseUrl.'/releases/tag/'.rawurlencode($tagName);
                                    @endphp
                                    <a href="{{ $tagUrl }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        View
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-3 text-slate-500" colspan="5">No global updates available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $updates->links() }}</div>
        </div>
    </div>
</x-central-layout>
