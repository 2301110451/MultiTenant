<?php

namespace App\Services;

use App\Models\DeploymentRun;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SafeDeploymentEngine
{
    public function deployApprovedRun(DeploymentRun $run): DeploymentRun
    {
        if ($run->status !== 'validated') {
            throw new RuntimeException('Deployment run is not validated.');
        }

        if ($run->environment === 'production' && ! (bool) config('deployments.allow_production_deploy', false)) {
            $run->forceFill([
                'status' => 'blocked',
                'failure_reason' => 'Production deployment is locked by configuration.',
            ])->save();

            throw new RuntimeException('Production deployment blocked by safety gate.');
        }

        if ((bool) config('deployments.dry_run', true)) {
            Log::warning('Deployment executed in dry-run mode.', [
                'deployment_run_id' => $run->id,
                'environment' => $run->environment,
                'strategy' => $run->strategy,
            ]);

            $run->forceFill([
                'status' => 'deployed_dry_run',
                'deployed_at' => now(),
            ])->save();

            return $run;
        }

        // Intentionally guarded: real deployment script integration should be attached here.
        // Example (future): switch traffic from blue to green only after health gates pass.
        $run->forceFill([
            'status' => 'deployed',
            'deployed_at' => now(),
        ])->save();

        return $run;
    }
}
