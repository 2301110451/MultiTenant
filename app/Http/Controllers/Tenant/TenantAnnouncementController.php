<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\TenantAnnouncementPublishedMail;
use App\Models\TenantAnnouncement;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TenantAnnouncementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $tenantUser = $request->user('tenant');
        abort_unless($tenantUser && $tenantUser->hasPermission('updates.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $announcement = TenantAnnouncement::query()->create([
            'title' => $data['title'],
            'message' => $data['message'],
            'is_active' => true,
            'published_at' => now(),
            'published_by' => $tenantUser->id,
        ]);

        $sentCount = 0;
        $failedCount = 0;

        if ((bool) ($data['send_email'] ?? true)) {
            $tenantName = (string) (Tenancy::currentTenant()?->name ?? config('app.name'));
            $publishedAt = optional($announcement->published_at)->format('M d, Y h:i A');

            User::query()
                ->where('is_active', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($announcement, $tenantName, $publishedAt, &$sentCount, &$failedCount): void {
                    foreach ($users as $user) {
                        try {
                            Mail::to((string) $user->email)->send(new TenantAnnouncementPublishedMail(
                                tenantName: $tenantName,
                                title: (string) $announcement->title,
                                messageBody: (string) $announcement->message,
                                publishedAt: $publishedAt,
                            ));
                            $sentCount++;
                        } catch (\Throwable $e) {
                            report($e);
                            $failedCount++;
                        }
                    }
                });

            $announcement->forceFill(['email_sent_at' => now()])->save();
        }

        $status = 'Announcement published.';
        if ($sentCount > 0 || $failedCount > 0) {
            $status .= " Email sent: {$sentCount}";
            if ($failedCount > 0) {
                $status .= ", failed: {$failedCount}";
            }
            $status .= '.';
        }

        return redirect()->route('tenant.updates.index')->with('status', $status);
    }
}
