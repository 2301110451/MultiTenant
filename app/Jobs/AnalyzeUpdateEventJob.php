<?php

namespace App\Jobs;

use App\Models\DeploymentCandidate;
use App\Models\UpdateEvent;
use App\Services\DeploymentRiskAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeUpdateEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $updateEventId,
    ) {}

    public function handle(DeploymentRiskAnalyzer $riskAnalyzer): void
    {
        $event = UpdateEvent::query()->find($this->updateEventId);
        if (! $event instanceof UpdateEvent) {
            return;
        }

        if (! in_array($event->status, ['received', 'analyzing'], true)) {
            return;
        }

        try {
            $event->forceFill(['status' => 'analyzing'])->save();

            $normalized = is_array($event->normalized) ? $event->normalized : [];
            $files = collect($normalized['files'] ?? [])
                ->map(static fn ($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $analysis = $riskAnalyzer->analyze($files);

            DB::connection('mysql')->transaction(function () use ($analysis, $event): void {
                DeploymentCandidate::query()->updateOrCreate(
                    ['update_event_id' => $event->id],
                    [
                        'risk_level' => $analysis['risk_level'],
                        'risk_score' => $analysis['risk_score'],
                        'change_summary' => $analysis['change_summary'],
                        'affected_modules' => $analysis['affected_modules'],
                        'blast_radius' => $analysis['blast_radius'],
                        'status' => 'pending_review',
                    ]
                );

                $event->forceFill([
                    'status' => 'analyzed',
                    'processed_at' => now(),
                    'processing_error' => null,
                ])->save();
            });
        } catch (\Throwable $exception) {
            Log::warning('Update event analysis failed.', [
                'update_event_id' => $event->id,
                'message' => $exception->getMessage(),
            ]);

            $event->forceFill([
                'status' => 'failed',
                'processing_error' => $exception->getMessage(),
                'processed_at' => now(),
            ])->save();
        }
    }
}
