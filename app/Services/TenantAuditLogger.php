<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

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
    }
}
