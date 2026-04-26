<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TriggerApprovedReleaseSyncOnWebRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('services.laptop_sync.web_request_sync_enabled', false)) {
            return $next($request);
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $sourceUrl = trim((string) config('services.laptop_sync.source_release_url', ''));
        if ($sourceUrl === '') {
            return $next($request);
        }

        $intervalSeconds = max(10, (int) config('services.laptop_sync.web_request_sync_interval_seconds', 60));
        $lastRunKey = 'laptop_sync:web_request:last_run_at';
        $lock = Cache::lock('laptop_sync:web_request:lock', 30);

        if (! $lock->get()) {
            return $next($request);
        }

        try {
            $lastRunAt = (int) Cache::get($lastRunKey, 0);
            if ($lastRunAt > 0 && (time() - $lastRunAt) < $intervalSeconds) {
                return $next($request);
            }

            Cache::put($lastRunKey, time(), now()->addHours(12));

            // Trigger safe sync. Command already validates cleanliness and ff-only merge rules.
            Artisan::call('system:sync-approved-release', [
                '--apply' => true,
            ]);
        } catch (Throwable) {
            // Keep user requests unaffected even if sync command fails.
        } finally {
            $lock->release();
        }

        return $next($request);
    }
}
