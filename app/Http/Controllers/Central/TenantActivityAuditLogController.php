<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantActivityAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TenantActivityAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'actor_key' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'in:active,inactive'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $tableReady = Schema::connection('mysql')->hasTable('tenant_activity_audit_logs');
        $selectedTenantId = isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null;
        [$actorTenantId, $actorUserId, $isActorSelectionValid] = $this->parseActorKey((string) ($validated['actor_key'] ?? ''));
        $hasTenantActorMismatch = $selectedTenantId !== null
            && $actorTenantId !== null
            && $selectedTenantId !== $actorTenantId;
        $actorFilterApplied = $isActorSelectionValid && ! $hasTenantActorMismatch;
        if (! $actorFilterApplied) {
            $actorTenantId = null;
            $actorUserId = null;
        }

        $baseQuery = $tableReady
            ? TenantActivityAuditLog::query()
                ->with('tenant:id,name')
                ->where(function (Builder $query): void {
                    $query->where('event_key', 'like', '%login%')
                        ->orWhere('event_key', 'like', '%logout%');
                })
                ->when($selectedTenantId !== null, function (Builder $query) use ($selectedTenantId): void {
                    $query->where('tenant_id', $selectedTenantId);
                })
                ->when($actorUserId !== null, function (Builder $query) use ($actorTenantId, $actorUserId): void {
                    $query->where('actor_user_id', $actorUserId);
                    if ($actorTenantId !== null) {
                        $query->where('tenant_id', $actorTenantId);
                    }
                })
                ->when(! empty($validated['status']), function ($query) use ($validated) {
                    $this->applyUserStatusFilter($query, (string) $validated['status']);
                })
                ->when(! empty($validated['date_from']), function ($query) use ($validated) {
                    $query->whereDate('created_at', '>=', (string) $validated['date_from']);
                })
                ->when(! empty($validated['date_to']), function ($query) use ($validated) {
                    $query->whereDate('created_at', '<=', (string) $validated['date_to']);
                })
            : null;

        $logs = $tableReady
            ? (clone $baseQuery)
                ->latest('id')
                ->paginate(25)
                ->withQueryString()
            : new LengthAwarePaginator(
                items: collect(),
                total: 0,
                perPage: 25,
                currentPage: LengthAwarePaginator::resolveCurrentPage(),
                options: [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        $trendRows = $tableReady
            ? (clone $baseQuery)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->map(fn ($row): array => [
                    'day' => (string) $row->day,
                    'total' => (int) $row->total,
                ])
                ->values()
            : collect();
        $trendData = $trendRows->count() > 14 ? $trendRows->slice(-14)->values() : $trendRows;
        $actionData = $tableReady
            ? (clone $baseQuery)
                ->selectRaw('event_key, COUNT(*) as total')
                ->groupBy('event_key')
                ->orderByDesc('total')
                ->limit(6)
                ->get()
                ->map(fn ($row): array => [
                    'event_key' => (string) $row->event_key,
                    'total' => (int) $row->total,
                ])
                ->values()
            : collect();

        $tenants = Tenant::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $actorMeta = $tableReady
            ? $this->resolveActorMetaForPage($logs->items())
            : ['states' => [], 'roles' => []];
        $actorOptions = $this->loadActorOptions(
            $selectedTenantId,
            (string) ($validated['status'] ?? '')
        );
        $selectedActorValue = (string) ($validated['actor_key'] ?? '');
        if (! $actorFilterApplied) {
            $selectedActorValue = '';
        }
        $selectedActorLabel = null;
        foreach ($actorOptions as $option) {
            if (($option['value'] ?? '') === $selectedActorValue) {
                $selectedActorLabel = (string) ($option['label'] ?? '');
                break;
            }
        }
        if ($selectedActorValue !== '' && $selectedActorLabel === null) {
            $selectedActorLabel = 'Invalid user selection';
        }
        $selectedTenantLabel = $selectedTenantId === null
            ? 'All tenants'
            : (string) ($tenants->firstWhere('id', $selectedTenantId)?->name ?? 'Unknown tenant');

        return view('central.audit-logs.index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'actorOptions' => $actorOptions,
            'actorStates' => $actorMeta['states'],
            'actorRoles' => $actorMeta['roles'],
            'selectedTenantLabel' => $selectedTenantLabel,
            'selectedActorLabel' => $selectedActorLabel ?: 'All users',
            'trendData' => $trendData,
            'actionData' => $actionData,
            'actorFilterReset' => ! $actorFilterApplied && ((string) ($validated['actor_key'] ?? '') !== ''),
            'tableReady' => $tableReady,
            'filters' => [
                'tenant_id' => $validated['tenant_id'] ?? '',
                'actor_key' => $selectedActorValue,
                'status' => $validated['status'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
            'timezoneLabel' => (string) config('app.timezone', 'UTC'),
        ]);
    }

    /**
     * @return array{0:int|null,1:int|null,2:bool}
     */
    private function parseActorKey(string $actorKey): array
    {
        $value = trim($actorKey);
        if ($value === '') {
            return [null, null, true];
        }

        if (preg_match('/^(\d+):(\d+)$/', $value, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2], true];
        }

        return [null, null, false];
    }

    private function applyUserStatusFilter(Builder $query, string $status): void
    {
        $isActive = $status === 'active';
        $tenantUserMap = $this->loadUserIdsByActiveStatus($isActive);
        if (empty($tenantUserMap)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $outer) use ($tenantUserMap): void {
            foreach ($tenantUserMap as $tenantId => $userIds) {
                if (empty($userIds)) {
                    continue;
                }

                $outer->orWhere(function (Builder $inner) use ($tenantId, $userIds): void {
                    $inner->where('tenant_id', $tenantId)
                        ->whereIn('actor_user_id', $userIds);
                });
            }
        });
    }

    /**
     * @param  array<int, TenantActivityAuditLog>  $logs
     * @return array{states: array<string, bool>, roles: array<string, string>}
     */
    private function resolveActorMetaForPage(array $logs): array
    {
        $pairs = [];
        foreach ($logs as $log) {
            if ($log->tenant_id === null || $log->actor_user_id === null) {
                continue;
            }

            $pairs[$log->tenant_id][] = (int) $log->actor_user_id;
        }

        if (empty($pairs)) {
            return ['states' => [], 'roles' => []];
        }

        $states = [];
        $roles = [];
        $originalTenantDb = (string) config('database.connections.tenant.database');
        $tenants = Tenant::query()->whereIn('id', array_keys($pairs))->get(['id', 'database']);
        foreach ($tenants as $tenant) {
            try {
                $tenant->configureTenantConnection();
                $userIds = array_values(array_unique($pairs[$tenant->id] ?? []));
                if (empty($userIds)) {
                    continue;
                }

                $rows = DB::connection('tenant')
                    ->table('users')
                    ->select(['id', 'is_active', 'role'])
                    ->whereIn('id', $userIds)
                    ->get();

                foreach ($rows as $row) {
                    $key = $tenant->id.':'.$row->id;
                    $states[$key] = (bool) $row->is_active;
                    $roles[$key] = is_string($row->role) ? $row->role : '';
                }
            } catch (\Throwable) {
                continue;
            }
        }

        config(['database.connections.tenant.database' => $originalTenantDb]);
        DB::purge('tenant');

        return ['states' => $states, 'roles' => $roles];
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function loadActorOptions(?int $selectedTenantId, string $statusFilter): array
    {
        $onlyActive = $statusFilter === 'active';
        $onlyInactive = $statusFilter === 'inactive';
        $options = [];
        $tenants = Tenant::query()
            ->when($selectedTenantId !== null, fn ($query) => $query->where('id', $selectedTenantId))
            ->orderBy('name')
            ->get(['id', 'name', 'database']);

        $originalTenantDb = (string) config('database.connections.tenant.database');

        foreach ($tenants as $tenant) {
            try {
                $tenant->configureTenantConnection();

                $query = DB::connection('tenant')
                    ->table('users')
                    ->select(['id', 'name', 'email', 'is_active'])
                    ->orderBy('name');

                if ($onlyActive) {
                    $query->where('is_active', true);
                } elseif ($onlyInactive) {
                    $query->where('is_active', false);
                }

                $users = $query->get();
                foreach ($users as $user) {
                    $options[] = [
                        'value' => $tenant->id.':'.$user->id,
                        'label' => $tenant->name.' - '.((string) $user->name).' ('.((string) $user->email).')',
                    ];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        config(['database.connections.tenant.database' => $originalTenantDb]);
        DB::purge('tenant');

        return $options;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function loadUserIdsByActiveStatus(bool $isActive): array
    {
        $result = [];
        $tenants = Tenant::query()->get(['id', 'database']);
        $originalTenantDb = (string) config('database.connections.tenant.database');

        foreach ($tenants as $tenant) {
            try {
                $tenant->configureTenantConnection();
                $userIds = DB::connection('tenant')
                    ->table('users')
                    ->where('is_active', $isActive)
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->values()
                    ->all();

                if (! empty($userIds)) {
                    $result[(int) $tenant->id] = $userIds;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        config(['database.connections.tenant.database' => $originalTenantDb]);
        DB::purge('tenant');

        return $result;
    }
}
