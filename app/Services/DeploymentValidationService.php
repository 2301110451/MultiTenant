<?php

namespace App\Services;

use App\Models\DeploymentRun;
use Illuminate\Support\Facades\Log;

class DeploymentValidationService
{
    public function queueValidation(DeploymentRun $run): DeploymentRun
    {
        // Phase-safe behavior: we record deterministic validation gates before deployment is allowed.
        $report = [
            'static_analysis' => 'pending',
            'dependency_integrity' => 'pending',
            'build_verification' => 'pending',
            'automated_tests' => 'pending',
            'runtime_smoke_tests' => 'pending',
        ];

        $run->forceFill([
            'status' => 'validation_queued',
            'validation_report' => $report,
        ])->save();

        Log::info('Deployment validation queued.', [
            'deployment_run_id' => $run->id,
            'environment' => $run->environment,
        ]);

        return $run;
    }

    public function markValidationResult(DeploymentRun $run, array $report, bool $passed): DeploymentRun
    {
        $run->forceFill([
            'status' => $passed ? 'validated' : 'validation_failed',
            'validation_report' => $report,
            'validated_at' => now(),
            'failure_reason' => $passed ? null : 'Validation pipeline failed.',
        ])->save();

        return $run;
    }
}
