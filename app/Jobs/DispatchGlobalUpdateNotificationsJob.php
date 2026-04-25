<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\GlobalUpdatePublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchGlobalUpdateNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<int>|null  $tenantIds
     */
    public function __construct(
        private readonly string $title,
        private readonly string $version,
        private readonly string $description,
        private readonly ?array $tenantIds = null,
    ) {}

    public function handle(): void
    {
        $tenantQuery = Tenant::query()->select(['id', 'name']);
        if (is_array($this->tenantIds) && $this->tenantIds !== []) {
            $tenantQuery->whereIn('id', $this->tenantIds);
        }

        $tenants = $tenantQuery->get();

        foreach ($tenants as $tenant) {
            try {
                $tenant->configureTenantConnection();

                User::query()
                    ->where('is_active', true)
                    ->chunkById(200, function ($users) use ($tenant): void {
                        foreach ($users as $user) {
                            try {
                                $user->notify(new GlobalUpdatePublishedNotification(
                                    $this->title,
                                    $this->version,
                                    $this->description
                                ));
                            } catch (\Throwable $exception) {
                                Log::warning('Failed to notify tenant user about global update.', [
                                    'tenant_id' => $tenant->id,
                                    'user_id' => $user->id,
                                    'message' => $exception->getMessage(),
                                ]);
                            }
                        }
                    });
            } catch (\Throwable $exception) {
                Log::warning('Failed to dispatch global update notifications for tenant.', [
                    'tenant_id' => $tenant->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
