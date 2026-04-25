<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\DeploymentCandidate;
use App\Models\DeploymentRun;
use App\Models\DeploymentSnapshot;
use App\Services\DeploymentUpdateIngestionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeploymentCandidateController extends Controller
{
    public function index(DeploymentUpdateIngestionService $ingestionService): View
    {
        $hasControlPlaneTables = Schema::connection('mysql')->hasTable('deployment_candidates')
            && Schema::connection('mysql')->hasTable('deployment_runs')
            && Schema::connection('mysql')->hasTable('deployment_snapshots')
            && Schema::connection('mysql')->hasTable('update_events');

        $latestDetectedCommit = null;
        $latestDetectedFiles = collect();

        if ($hasControlPlaneTables) {
            // UI-driven auto-sync: no terminal command required for normal GitHub detection.
            $ingestionService->syncIfDue();

            $candidates = DeploymentCandidate::query()
                ->with('updateEvent')
                ->whereNotIn('status', ['rejected', 'rejected_archived'])
                ->latest('id')
                ->paginate(20);

            $runs = DeploymentRun::query()
                ->with(['candidate.updateEvent', 'snapshot'])
                ->latest('id')
                ->limit(25)
                ->get();

            $snapshots = DeploymentSnapshot::query()
                ->latest('id')
                ->limit(25)
                ->get();

            $latestCandidate = DeploymentCandidate::query()
                ->with('updateEvent')
                ->latest('id')
                ->first();

            $latestEvent = $latestCandidate?->updateEvent;
            if ($latestEvent !== null && is_array($latestEvent->normalized ?? null)) {
                $normalized = $latestEvent->normalized;
                $latestDetectedCommit = (string) ($latestEvent->commit_sha ?: ($latestEvent->tag ?: ''));
                $latestDetectedFiles = collect($normalized['files'] ?? [])
                    ->map(static fn ($file) => trim((string) $file))
                    ->filter()
                    ->values();
            }
        } else {
            $candidates = new LengthAwarePaginator(
                collect(),
                0,
                20,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
            $runs = collect();
            $snapshots = collect();
        }

        return view('central.global-updates.candidates', [
            'candidates' => $candidates,
            'runs' => $runs,
            'snapshots' => $snapshots,
            'missingControlPlaneTables' => ! $hasControlPlaneTables,
            'latestDetectedCommit' => $latestDetectedCommit,
            'latestDetectedFiles' => $latestDetectedFiles,
        ]);
    }

    public function approve(Request $request, DeploymentCandidate $candidate): RedirectResponse
    {
        if (! $this->canUseControlPlane()) {
            return back()->with('error', 'Deployment control-plane tables are missing. Run migrations first.');
        }

        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $data = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:500'],
        ]);

        $candidate->forceFill([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'decision_note' => $data['decision_note'] ?? null,
        ])->save();

        return back()->with('success', 'Deployment candidate approved.');
    }

    public function reject(Request $request, DeploymentCandidate $candidate): RedirectResponse
    {
        if (! $this->canUseControlPlane()) {
            return back()->with('error', 'Deployment control-plane tables are missing. Run migrations first.');
        }

        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $data = $request->validate([
            'decision_note' => ['required', 'string', 'max:500'],
        ]);

        $candidate->forceFill([
            'status' => 'rejected',
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'decision_note' => $data['decision_note'],
        ])->save();

        return back()->with('success', 'Deployment candidate rejected.');
    }

    public function rejectedIndex(): View
    {
        if (! $this->canUseControlPlane()) {
            $candidates = new LengthAwarePaginator(
                collect(),
                0,
                20,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return view('central.global-updates.rejected', [
                'candidates' => $candidates,
                'missingControlPlaneTables' => true,
            ]);
        }

        $candidates = DeploymentCandidate::query()
            ->with('updateEvent')
            ->whereIn('status', ['rejected', 'rejected_archived'])
            ->latest('id')
            ->paginate(20);

        return view('central.global-updates.rejected', [
            'candidates' => $candidates,
            'missingControlPlaneTables' => false,
        ]);
    }

    public function restoreToCandidates(Request $request, DeploymentCandidate $candidate): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        if (! in_array($candidate->status, ['rejected', 'rejected_archived'], true)) {
            return back()->with('error', 'Only rejected candidates can be restored.');
        }

        $candidate->forceFill([
            'status' => 'pending_review',
            'rejected_by' => null,
            'rejected_at' => null,
        ])->save();

        return back()->with('success', 'Candidate restored to deployment candidates.');
    }

    private function canUseControlPlane(): bool
    {
        return Schema::connection('mysql')->hasTable('deployment_candidates')
            && Schema::connection('mysql')->hasTable('update_events');
    }
}
