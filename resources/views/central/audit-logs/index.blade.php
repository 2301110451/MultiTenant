<x-central-layout title="Audit Logs" breadcrumb="Audit Logs">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tenant Activity Audit Logs</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Review tenant activity across all barangays with timestamp, user, action, resource, changes, and status.
        </p>

        @if(! $tableReady)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                Audit log table is not ready yet. Run <code>php artisan migrate</code> to enable centralized audit logging.
            </div>
        @endif

        <form method="GET" action="{{ route('central.audit-logs.index') }}" class="c-card p-4 sm:p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label for="tenant_id" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tenant</label>
                <select id="tenant_id" name="tenant_id" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected((string) $filters['tenant_id'] === (string) $tenant->id)>
                            {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="actor" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">User</label>
                <select id="actor" name="actor_key" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All users</option>
                    @foreach($actorOptions as $option)
                        <option value="{{ $option['value'] }}" @selected($filters['actor_key'] === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</label>
                <select id="status" name="status" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All statuses</option>
                    <option value="active" @selected($filters['status'] === 'active')>Active</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                </select>
            </div>

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
                    Apply filters
                </button>
            </div>
        </form>

        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
            Showing logs for:
            <span class="font-semibold">Tenant:</span> {{ $selectedTenantLabel }}
            <span class="mx-2">|</span>
            <span class="font-semibold">User:</span> {{ $selectedActorLabel }}
            <span class="mx-2">|</span>
            <span class="font-semibold">Rows:</span> {{ $logs->total() }}
        </div>

        @if($actorFilterReset)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                User filter was reset because it did not match the selected tenant. Choose a user again if needed.
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="c-card p-4 sm:p-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Audit Activity Trend</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Based on currently applied filters (last 14 days with data).</p>
                @php
                    $trendMax = max(1, ...array_map(static fn ($item): int => (int) $item['total'], $trendData->all() ?: [['total' => 0]]));
                @endphp
                @if($trendData->isEmpty())
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">No trend data for current filters.</p>
                @else
                    <div class="mt-4 flex items-end gap-2 h-36">
                        @foreach($trendData as $point)
                            @php
                                $height = max(8, (int) round((((int) $point['total']) / $trendMax) * 100));
                            @endphp
                            <div class="flex-1 min-w-0 flex flex-col items-center gap-1">
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $point['total'] }}</div>
                                <div class="w-full rounded-t bg-indigo-500/80 dark:bg-indigo-400/80" style="height: {{ $height }}px;"></div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Carbon::parse($point['day'])->format('M d') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="c-card p-4 sm:p-5">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Top Audit Actions</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Most frequent actions for current filters.</p>
                @php
                    $actionMax = max(1, ...array_map(static fn ($item): int => (int) $item['total'], $actionData->all() ?: [['total' => 0]]));
                @endphp
                @if($actionData->isEmpty())
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">No action data for current filters.</p>
                @else
                    <div class="mt-4 space-y-2">
                        @foreach($actionData as $row)
                            @php $width = max(6, (int) round((((int) $row['total']) / $actionMax) * 100)); @endphp
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-300">
                                    <span>{{ $row['event_key'] }}</span>
                                    <span class="font-semibold">{{ $row['total'] }}</span>
                                </div>
                                <div class="mt-1 h-2 rounded bg-slate-200 dark:bg-slate-700">
                                    <div class="h-2 rounded bg-violet-500 dark:bg-violet-400" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="c-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Log In / Log Out</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $log->tenant?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">
                                @php
                                    $stateKey = ((int) ($log->tenant_id ?? 0)).':'.((int) ($log->actor_user_id ?? 0));
                                    $role = $actorRoles[$stateKey] ?? (is_array($log->metadata) ? (string) ($log->metadata['actor_role'] ?? '') : '');
                                @endphp
                                <div class="font-medium">{{ $log->actor_name ?? 'System' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Role: {{ $role !== '' ? str_replace('_', ' ', ucwords($role, '_')) : '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $log->actor_user_id ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $log->actor_email ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-800 dark:text-slate-200">
                                @php
                                    $eventKey = strtolower((string) $log->event_key);
                                    $actionLabel = str_contains($eventKey, 'logout')
                                        ? 'Log Out'
                                        : 'Log In';
                                @endphp
                                <div class="font-semibold">{{ $actionLabel }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $log->created_at?->timezone($timezoneLabel)->format('M d, Y h:i:s A') ?? '—' }} ({{ $timezoneLabel }})
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $isActive = $actorStates[$stateKey] ?? null;
                                @endphp
                                @if($isActive === null)
                                    <span class="text-xs text-slate-500 dark:text-slate-400">—</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $isActive ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300' : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-slate-500 dark:text-slate-400" colspan="4">No audit logs found for the selected filters.</td>
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
