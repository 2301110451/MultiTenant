<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Support\TenantAppearance;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user && ($user->isSecretary() || $user->isCaptain()), 403);

        abort_unless(
            TenantAppearance::planAllowsReports(),
            403,
            'Reports and analytics are not included in your barangay subscription plan.'
        );

        $topFacilities = Facility::query()
            ->withCount('reservations')
            ->orderByDesc('reservations_count')
            ->take(5)
            ->get();

        $peakDays = Reservation::query()
            ->select(DB::raw('DAYNAME(starts_at) as day_name'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DAYNAME(starts_at)'))
            ->orderByDesc('total')
            ->get();

        $revenue = Payment::query()
            ->where('status', 'paid')
            ->sum('amount');

        $damageCount = DamageReport::query()->count();

        return view('tenant.reports.index', compact(
            'topFacilities',
            'peakDays',
            'revenue',
            'damageCount'
        ));
    }
}
