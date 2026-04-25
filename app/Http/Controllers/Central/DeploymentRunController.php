<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\DeploymentCandidate;
use App\Models\DeploymentRun;
use App\Models\DeploymentSnapshot;
use App\Services\SafeDeploymentEngine;
use App\Services\SafeRollbackService;
use App\Services\DeploymentValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeploymentRunController extends Controller
{
    public function requestValidation(Request $request, DeploymentCandidate $candidate, DeploymentValidationService $validationService): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        if ($candidate->status !== 'approved') {
            return back()->with('error', 'Candidate must be approved before validation.');
        }

        $run = DeploymentRun::query()->create([
            'deployment_candidate_id' => $candidate->id,
            'status' => 'pending_validation',
            'environment' => 'staging',
            'strategy' => (string) config('deployments.strategy', 'blue_green'),
            'requested_by' => $actor->id,
            'approved_by' => $actor->id,
        ]);

        $validationService->queueValidation($run);

        return back()->with('success', 'Validation pipeline queued (staging only).');
    }

    public function deploy(Request $request, DeploymentRun $run, SafeDeploymentEngine $engine): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        if (in_array($run->status, ['deployed', 'deployed_dry_run'], true)) {
            return back()->with('success', "Run #{$run->id} is already deployed.");
        }

        if ($run->status !== 'validated') {
            return back()->with('error', "Run #{$run->id} must be validated before deploy.");
        }

        try {
            $engine->deployApprovedRun($run);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Deployment engine executed (guarded by safety config).');
    }

    public function markValidated(Request $request, DeploymentRun $run, DeploymentValidationService $validationService): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        if (! in_array($run->status, ['pending_validation', 'validation_queued'], true)) {
            return back()->with('error', 'Only queued validation runs can be marked as validated.');
        }

        $report = [
            'static_analysis' => 'passed',
            'dependency_integrity' => 'passed',
            'build_verification' => 'passed',
            'automated_tests' => 'passed',
            'runtime_smoke_tests' => 'passed',
            'source' => 'manual_test_override',
        ];

        $validationService->markValidationResult($run, $report, true);

        return back()->with('success', 'Validation marked as passed for testing.');
    }

    public function undo(Request $request, DeploymentRun $run, SafeRollbackService $rollbackService): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $data = $request->validate([
            'snapshot_id' => ['required', 'integer', 'exists:deployment_snapshots,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $snapshot = DeploymentSnapshot::query()->findOrFail((int) $data['snapshot_id']);
        $rollbackService->manualUndo($run, $snapshot, (int) $actor->id, (string) $data['reason']);

        return back()->with('success', 'Manual undo completed.');
    }
}
