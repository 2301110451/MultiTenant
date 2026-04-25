<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeUpdateEventJob;
use App\Models\UpdateEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GitHubUpdateWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = trim((string) config('services.github.webhook_secret'));
        $rawPayload = $request->getContent();
        $signature = (string) $request->header('X-Hub-Signature-256', '');
        $deliveryId = trim((string) $request->header('X-GitHub-Delivery', ''));
        $eventType = trim((string) $request->header('X-GitHub-Event', ''));

        if ($secret === '' || ! $this->validSignature($rawPayload, $signature, $secret)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        if ($deliveryId === '' || $eventType === '') {
            return response()->json(['message' => 'Missing required GitHub headers.'], 422);
        }

        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();
        $normalized = $this->normalizeEvent($eventType, $payload);

        $created = false;

        DB::connection('mysql')->transaction(function () use (
            $deliveryId,
            $eventType,
            $payload,
            $normalized,
            &$created
        ): void {
            $existing = UpdateEvent::query()->where('delivery_id', $deliveryId)->first();
            if ($existing !== null) {
                return;
            }

            $event = UpdateEvent::query()->create([
                'source' => 'github',
                'delivery_id' => $deliveryId,
                'event_type' => $eventType,
                'ref' => $normalized['ref'] ?? null,
                'commit_sha' => $normalized['commit_sha'] ?? null,
                'tag' => $normalized['tag'] ?? null,
                'payload' => $payload,
                'normalized' => $normalized,
                'status' => 'received',
                'received_at' => now(),
            ]);

            if ((bool) config('deployments.process_events_inline', true)) {
                AnalyzeUpdateEventJob::dispatchSync($event->id);
            } else {
                AnalyzeUpdateEventJob::dispatch($event->id)->onQueue('default');
            }
            $created = true;
        });

        return response()->json([
            'accepted' => true,
            'duplicate' => ! $created,
        ], 202);
    }

    private function validSignature(string $rawPayload, string $signatureHeader, string $secret): bool
    {
        if (! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeEvent(string $eventType, array $payload): array
    {
        if ($eventType === 'push') {
            $commits = collect($payload['commits'] ?? [])->filter(fn ($item): bool => is_array($item));
            $files = $commits->flatMap(function (array $commit): array {
                return array_values(array_unique(array_merge(
                    $this->toArray($commit['added'] ?? []),
                    $this->toArray($commit['modified'] ?? []),
                    $this->toArray($commit['removed'] ?? [])
                )));
            })->filter()->values()->all();

            return [
                'kind' => 'commit',
                'ref' => (string) ($payload['ref'] ?? ''),
                'commit_sha' => (string) ($payload['after'] ?? ''),
                'tag' => null,
                'files' => $files,
                'commit_count' => $commits->count(),
                'head_commit_message' => (string) ($payload['head_commit']['message'] ?? ''),
                'pusher' => (string) ($payload['pusher']['name'] ?? ''),
            ];
        }

        if ($eventType === 'release') {
            $release = is_array($payload['release'] ?? null) ? $payload['release'] : [];

            return [
                'kind' => 'release',
                'ref' => null,
                'commit_sha' => (string) ($release['target_commitish'] ?? ''),
                'tag' => (string) ($release['tag_name'] ?? ''),
                'files' => [],
                'release_name' => (string) ($release['name'] ?? ''),
            ];
        }

        return [
            'kind' => 'other',
            'ref' => (string) ($payload['ref'] ?? ''),
            'commit_sha' => (string) ($payload['after'] ?? ''),
            'tag' => null,
            'files' => [],
        ];
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function toArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(static fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }
}
