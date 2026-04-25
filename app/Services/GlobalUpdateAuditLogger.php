<?php

namespace App\Services;

use App\Models\CentralUser;
use App\Models\GlobalUpdateAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GlobalUpdateAuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        Request $request,
        ?CentralUser $actor,
        string $action,
        string $status,
        ?string $message = null,
        ?string $scope = null,
        ?string $updateType = null,
        ?string $version = null,
        ?int $githubReleaseId = null,
        array $metadata = []
    ): void {
        try {
            GlobalUpdateAuditLog::query()->create([
                'actor_user_id' => $actor?->id,
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'scope' => $scope,
                'update_type' => $updateType,
                'version' => $version,
                'github_release_id' => $githubReleaseId,
                'metadata' => $metadata,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to persist global update audit log.', [
                'action' => $action,
                'status' => $status,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
