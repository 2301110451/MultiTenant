<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Release;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaptopSyncReleaseController extends Controller
{
    public function latestApproved(Request $request): JsonResponse
    {
        $expectedToken = trim((string) config('services.laptop_sync.token', ''));
        $providedToken = trim((string) $request->header('X-Laptop-Sync-Token', ''));

        // If a token is configured, require an exact match.
        if ($expectedToken !== '' && ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized sync request.',
            ], 401);
        }

        $release = Release::query()
            ->whereIn('status', ['approved', 'published'])
            ->whereNotNull('source_commit_sha')
            ->latest('id')
            ->first();

        if (! $release instanceof Release) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No approved release is available yet.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $release->id,
                'status' => $release->status,
                'version' => $release->version,
                'suggested_version' => $release->suggested_version,
                'source_commit_sha' => $release->source_commit_sha,
                'approved_at' => optional($release->approved_at)?->toIso8601String(),
                'published_at' => optional($release->published_at)?->toIso8601String(),
            ],
        ]);
    }
}
