<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\DeploymentCandidate;
use App\Models\SupportTicket;
use App\Models\TenantSubscriptionIntent;
use App\Support\CentralDashboardMetrics;
use Illuminate\Http\JsonResponse;

class RealtimeController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $stats = CentralDashboardMetrics::mainDashboardStats();

        return response()->json([
            'tenantCount' => $stats['tenantCount'],
            'activeTenants' => $stats['activeTenants'],
            'suspendedTenants' => $stats['suspendedTenants'],
            'subscribedTenants' => $stats['subscribedTenants'],
            'version' => now()->timestamp,
        ]);
    }

    public function tenants(): JsonResponse
    {
        $stats = CentralDashboardMetrics::tenantsTabStats();

        return response()->json([
            'tenantCount' => $stats['tenantCount'],
            'activeTenants' => $stats['activeTenants'],
            'suspendedTenants' => $stats['suspendedTenants'],
            'unsubscribedTenants' => $stats['unsubscribedTenants'],
            'version' => now()->timestamp,
        ]);
    }

    public function subscriptionIntents(): JsonResponse
    {
        $row = TenantSubscriptionIntent::query()
            ->selectRaw("coalesce(sum(case when status = 'pending' then 1 else 0 end), 0) as pending")
            ->selectRaw("coalesce(sum(case when status = 'approved' then 1 else 0 end), 0) as approved")
            ->selectRaw("coalesce(sum(case when status = 'rejected' then 1 else 0 end), 0) as rejected")
            ->first();

        return response()->json([
            'pendingCount' => (int) ($row->pending ?? 0),
            'approvedCount' => (int) ($row->approved ?? 0),
            'rejectedCount' => (int) ($row->rejected ?? 0),
            'version' => now()->timestamp,
        ]);
    }

    public function supportTickets(): JsonResponse
    {
        $row = SupportTicket::query()
            ->selectRaw("coalesce(sum(case when status = 'open' then 1 else 0 end), 0) as open_count")
            ->selectRaw("coalesce(sum(case when status = 'in_progress' then 1 else 0 end), 0) as in_progress")
            ->selectRaw("coalesce(sum(case when status in ('resolved','closed') then 1 else 0 end), 0) as resolved")
            ->first();

        return response()->json([
            'openCount' => (int) ($row->open_count ?? 0),
            'inProgressCount' => (int) ($row->in_progress ?? 0),
            'resolvedCount' => (int) ($row->resolved ?? 0),
            'version' => now()->timestamp,
        ]);
    }

    public function deploymentCandidates(): JsonResponse
    {
        $row = DeploymentCandidate::query()
            ->selectRaw("coalesce(sum(case when status = 'pending_review' then 1 else 0 end), 0) as pending_review")
            ->selectRaw("coalesce(sum(case when status = 'approved' then 1 else 0 end), 0) as approved")
            ->selectRaw("coalesce(sum(case when status = 'rejected' then 1 else 0 end), 0) as rejected")
            ->first();

        return response()->json([
            'pendingReviewCount' => (int) ($row->pending_review ?? 0),
            'approvedCount' => (int) ($row->approved ?? 0),
            'rejectedCount' => (int) ($row->rejected ?? 0),
            'version' => now()->timestamp,
        ]);
    }
}
