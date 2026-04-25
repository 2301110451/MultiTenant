<?php

namespace App\Services;

use App\Models\DeploymentRun;
use App\Models\DeploymentSnapshot;
use App\Models\RollbackRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SafeRollbackService
{
    public function autoRollback(DeploymentRun $run, string $reason): RollbackRun
    {
        $snapshot = DeploymentSnapshot::query()
            ->where('is_stable', true)
            ->latest('id')
            ->first();

        if (! $snapshot instanceof DeploymentSnapshot) {
            throw new RuntimeException('No stable snapshot available for rollback.');
        }

        return $this->executeRollback($run, $snapshot, 'automatic', null, $reason);
    }

    public function manualUndo(DeploymentRun $run, DeploymentSnapshot $snapshot, int $actorId, string $reason): RollbackRun
    {
        return $this->executeRollback($run, $snapshot, 'manual', $actorId, $reason);
    }

    private function executeRollback(
        DeploymentRun $run,
        DeploymentSnapshot $snapshot,
        string $triggerType,
        ?int $actorId,
        string $reason
    ): RollbackRun {
        return DB::connection('mysql')->transaction(function () use ($run, $snapshot, $triggerType, $actorId, $reason): RollbackRun {
            $rollback = RollbackRun::query()->create([
                'deployment_run_id' => $run->id,
                'to_snapshot_id' => $snapshot->id,
                'trigger_type' => $triggerType,
                'status' => 'started',
                'triggered_by' => $actorId,
                'reason' => $reason,
                'started_at' => now(),
            ]);

            if ((bool) config('deployments.dry_run', true)) {
                Log::warning('Rollback executed in dry-run mode.', [
                    'rollback_run_id' => $rollback->id,
                    'snapshot_version' => $snapshot->version,
                ]);
            }

            $run->forceFill([
                'status' => 'rolled_back',
                'rolled_back_at' => now(),
                'failure_reason' => $reason,
            ])->save();

            $rollback->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
            ])->save();

            return $rollback;
        });
    }
}
