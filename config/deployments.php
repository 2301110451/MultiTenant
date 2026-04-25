<?php

return [
    // Hard safety gate: production deploy is blocked unless explicitly enabled.
    'allow_production_deploy' => filter_var(env('ALLOW_PRODUCTION_DEPLOY', 'false'), FILTER_VALIDATE_BOOLEAN),

    // If true, deploy/rollback commands are logged but not executed.
    'dry_run' => filter_var(env('DEPLOYMENT_DRY_RUN', 'true'), FILTER_VALIDATE_BOOLEAN),

    // Preferred strategy; currently blue/green only in this control-plane.
    'strategy' => env('DEPLOYMENT_STRATEGY', 'blue_green'),

    // Process update events immediately (no queue dependency) for deterministic visibility in admin UI.
    'process_events_inline' => filter_var(env('DEPLOYMENT_PROCESS_EVENTS_INLINE', 'true'), FILTER_VALIDATE_BOOLEAN),

    // Health thresholds used by auto-rollback policies.
    'auto_rollback' => [
        'enabled' => filter_var(env('AUTO_ROLLBACK_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN),
        'max_error_rate_percent' => (float) env('AUTO_ROLLBACK_MAX_ERROR_RATE_PERCENT', 5),
        'max_p95_latency_ms' => (int) env('AUTO_ROLLBACK_MAX_P95_MS', 1500),
    ],
];
