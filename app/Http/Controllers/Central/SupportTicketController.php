<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\SupportTicketStatusUpdatedMail;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(): View
    {
        $tickets = SupportTicket::query()->with('tenant')->latest()->paginate(30);

        return view('central/support-tickets/index', compact('tickets'));
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $previousStatus = (string) $supportTicket->status;
        $newStatus = $data['status'];

        $supportTicket->loadMissing('tenant');
        $supportTicket->update([
            'status' => $newStatus,
            'resolved_at' => in_array($newStatus, ['resolved', 'closed'], true) ? now() : null,
            'assigned_to' => $request->user()->id,
        ]);

        if ($previousStatus !== $newStatus && filter_var($supportTicket->requester_email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($supportTicket->requester_email)->send(
                    new SupportTicketStatusUpdatedMail($supportTicket->fresh(['tenant']), $previousStatus, $newStatus)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('central.support-tickets.index')->with('success', 'Ticket updated.');
    }
}
