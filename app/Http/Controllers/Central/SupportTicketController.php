<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\SupportTicketStatusUpdatedMail;
use App\Models\CentralUser;
use App\Models\GlobalUpdateAuditLog;
use App\Models\SupportTicket;
use App\Services\GlobalUpdateAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly GlobalUpdateAuditLogger $auditLogger,
    ) {}

    public function index(): View
    {
        $tickets = SupportTicket::query()->with('tenant')->latest()->paginate(30);
        $releaseActivities = GlobalUpdateAuditLog::query()
            ->with('actor:id,name')
            ->where('action', 'like', 'release.%')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('central/support-tickets/index', compact('tickets', 'releaseActivities'));
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $actor = $request->user('web');
        if (! $actor instanceof CentralUser) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $previousStatus = (string) $supportTicket->status;
        $newStatus = $data['status'];

        $supportTicket->loadMissing('tenant');
        $supportTicket->update([
            'status' => $newStatus,
            'resolved_at' => in_array($newStatus, ['resolved', 'closed'], true) ? now() : null,
            'assigned_to' => $actor->id,
        ]);

        $this->auditLogger->log(
            $request,
            $actor,
            'support_ticket.update',
            'success',
            "Support ticket #{$supportTicket->id} status changed from {$previousStatus} to {$newStatus}.",
            (string) ($supportTicket->tenant?->name ?? 'all_tenants'),
            null,
            null,
            null,
            [
                'record' => 'ticket#'.$supportTicket->id.' '.$supportTicket->subject,
                'old_status' => $previousStatus,
                'new_status' => $newStatus,
            ]
        );

        if ($previousStatus !== $newStatus && filter_var($supportTicket->requester_email, FILTER_VALIDATE_EMAIL)) {
            $mailFailed = false;
            try {
                // Send immediately so reporter receives status updates without requiring a queue worker.
                Mail::to($supportTicket->requester_email)->send(
                    new SupportTicketStatusUpdatedMail($supportTicket->fresh(['tenant']), $previousStatus, $newStatus)
                );
            } catch (\Throwable $e) {
                report($e);
                $mailFailed = true;
            }

            if ($mailFailed) {
                return redirect()
                    ->route('central.support-tickets.index')
                    ->with('success', 'Ticket updated.')
                    ->with('error', 'Ticket updated but status email could not be sent. Check mail settings/logs.');
            }
        }

        return redirect()->route('central.support-tickets.index')->with('success', 'Ticket updated.');
    }
}
