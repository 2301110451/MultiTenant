<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\GlobalUpdateAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TenantActivityAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'actor' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:255'],
            'tenant' => ['nullable', 'string', 'max:255'],
        ]);

        $tableReady = Schema::connection('mysql')->hasTable('global_update_audit_logs');
        $query = $tableReady
            ? GlobalUpdateAuditLog::query()
                ->with('actor:id,name,email')
                ->when(! empty($validated['date_from']), function (Builder $builder) use ($validated): void {
                    $builder->whereDate('created_at', '>=', (string) $validated['date_from']);
                })
                ->when(! empty($validated['date_to']), function (Builder $builder) use ($validated): void {
                    $builder->whereDate('created_at', '<=', (string) $validated['date_to']);
                })
                ->when(! empty($validated['actor']), function (Builder $builder) use ($validated): void {
                    $actor = trim((string) $validated['actor']);
                    $builder->whereHas('actor', function (Builder $actorQuery) use ($actor): void {
                        $actorQuery->where('name', 'like', "%{$actor}%")
                            ->orWhere('email', 'like', "%{$actor}%");
                    });
                })
                ->when(! empty($validated['action']), function (Builder $builder) use ($validated): void {
                    $builder->where('action', 'like', '%'.trim((string) $validated['action']).'%');
                })
                ->when(! empty($validated['tenant']), function (Builder $builder) use ($validated): void {
                    $builder->where('scope', 'like', '%'.trim((string) $validated['tenant']).'%');
                })
            : null;

        $logs = $tableReady
            ? $query->latest('id')->paginate(30)->withQueryString()
            : GlobalUpdateAuditLog::query()->whereRaw('1 = 0')->paginate(30);

        return view('central.audit-logs.index', [
            'tableReady' => $tableReady,
            'logs' => $logs,
            'filters' => [
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'actor' => $validated['actor'] ?? '',
                'action' => $validated['action'] ?? '',
                'tenant' => $validated['tenant'] ?? '',
            ],
            'timezoneLabel' => (string) config('app.timezone', 'UTC'),
        ]);
    }
}
