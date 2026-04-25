<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantActivityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantAuditLogger
{
    public function log(Request $request, string $action, ?string $targetType = null, ?int $targetId = null, array $metadata = []): void
    {
        AuditLog::query()->create([
            'actor_user_id' => $request->user('tenant')?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $this->logToCentral($request, $action, $targetType, $targetId, $metadata);
    }

    private function logToCentral(Request $request, string $eventKey, ?string $targetType, ?int $targetId, array $metadata): void
    {
        try {
            $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            $actor = $request->user('tenant');

            [$module, $action] = $this->splitEventKey($eventKey);
            $status = $this->normalizeStatus($metadata['status'] ?? null);
            $beforeValues = $this->extractValues($metadata, ['before_values', 'before', 'old_values', 'old']);
            $afterValues = $this->extractValues($metadata, ['after_values', 'after', 'new_values', 'new']);

            TenantActivityAuditLog::query()->create([
                'tenant_id' => $tenant instanceof Tenant ? (int) $tenant->id : null,
                'actor_type' => $actor ? 'tenant_user' : 'system',
                'actor_user_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'actor_email' => $actor?->email,
                'module' => $module,
                'action' => $action,
                'event_key' => $eventKey,
                'status' => $status,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'target_label' => $this->resolveTargetLabel($metadata),
                'before_values' => $beforeValues,
                'after_values' => $afterValues,
                'metadata' => array_merge($metadata, [
                    'actor_role' => $this->resolveActorRole($actor),
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        } catch (\Throwable $exception) {
            // Central log is best-effort only; never block tenant actions.
            Log::warning('Failed to persist tenant activity audit log.', [
                'event_key' => $eventKey,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{0:string,1:string}
     */
    private function splitEventKey(string $eventKey): array
    {
        $parts = explode('.', $eventKey, 2);
        if (count($parts) < 2) {
            return ['general', $eventKey];
        }

        return [$parts[0], $parts[1]];
    }

    private function resolveTargetLabel(array $metadata): ?string
    {
        foreach (['target_label', 'name', 'email', 'title'] as $key) {
            $value = $metadata[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function resolveActorRole(mixed $actor): ?string
    {
        if (! $actor || ! isset($actor->role)) {
            return null;
        }

        $role = $actor->role;
        if (is_object($role) && isset($role->value) && is_string($role->value)) {
            return $role->value;
        }

        return is_string($role) ? $role : null;
    }

    private function normalizeStatus(mixed $status): string
    {
        $value = is_string($status) ? strtolower(trim($status)) : '';

        return in_array($value, ['success', 'failed'], true) ? $value : 'success';
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<int, string>  $keys
     * @return array<string, mixed>|null
     */
    private function extractValues(array $metadata, array $keys): ?array
    {
        foreach ($keys as $key) {
            $value = $metadata[$key] ?? null;
            if (is_array($value)) {
                return $value;
            }
        }

        return null;
    }
}
