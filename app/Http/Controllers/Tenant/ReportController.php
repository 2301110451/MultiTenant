<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\Reservation;
use App\Support\Pricing;
use App\Support\TenantAppearance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->assertReportsAccess();

        return view('tenant.reports.index', $this->reportData());
    }

    public function download(): Response|RedirectResponse
    {
        $user = $this->assertReportsAccess();
        $data = $this->reportData();
        $tenant = app('currentTenant');

        $pdf = Pdf::loadView('tenant.reports.pdf', array_merge($data, [
            'generatedAt' => now(),
            'generatedBy' => $user->name,
            'tenantName' => $tenant?->name ?? 'Barangay',
        ]));

        $filename = 'report-'.str($tenant?->name ?? 'barangay')->slug('-').'-'.now()->format('Ymd-His').'.pdf';
        $pdfBinary = $pdf->output();

        if (! empty($user->email)) {
            try {
                Mail::send('emails.report-pdf-ready', [
                    'userName' => $user->name,
                    'tenantName' => $tenant?->name ?? 'Barangay',
                ], function ($message) use ($user, $filename, $pdfBinary): void {
                    $message
                        ->to($user->email)
                        ->subject('Your tenant report PDF')
                        ->attachData($pdfBinary, $filename, ['mime' => 'application/pdf']);
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function downloadCsv(): Response
    {
        $user = $this->assertReportsAccess();
        $this->assertFeature('export_reports_csv');

        $rows = $this->exportRows($this->reportData(), $user->name);
        $filename = 'report-'.now()->format('Ymd-His').'.csv';

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function downloadExcel(): Response
    {
        $user = $this->assertReportsAccess();
        $this->assertFeature('export_reports_excel');
        $data = $this->reportData();
        $rows = $this->exportRows($data, $user->name);
        $filename = 'report-'.now()->format('Ymd-His').'.xls';

        $html = view('tenant.reports.excel', [
            'rows' => $rows,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function assertReportsAccess()
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user, 403);
        Gate::forUser($user)->authorize('tenant.reports.view');
        abort_unless(
            TenantAppearance::planAllowsReports(),
            403,
            'Reports and analytics are not included in your barangay subscription plan.'
        );

        return $user;
    }

    private function reportData(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

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

        $paidRevenue = (float) Payment::query()
            ->where('status', 'paid')
            ->sum('amount');

        $completedReservationRevenue = Reservation::query()
            ->with('facility:id,hourly_rate')
            ->where('status', 'completed')
            ->get()
            ->sum(function (Reservation $reservation): float {
                if ($reservation->revenue_amount !== null) {
                    return (float) $reservation->revenue_amount;
                }

                $minutes = max(0, (int) $reservation->starts_at?->diffInMinutes($reservation->ends_at ?? $reservation->starts_at));
                if ($minutes === 0) {
                    return 0.0;
                }

                $hours = $minutes / 60;
                $rate = (float) ($reservation->facility?->hourly_rate ?? 0);

                return round($hours * $rate, 2);
            });

        $revenue = $paidRevenue + (float) $completedReservationRevenue;

        // Count both legacy damage reports and reservation-linked damage charges.
        $damageCount = max(
            (int) DamageReport::query()->count(),
            (int) Payment::query()->where('method', 'damage')->count()
        );
        $damageChargeCount = Payment::query()
            ->where('method', 'damage')
            ->count();
        $pendingDamagePayments = Payment::query()
            ->where('method', 'damage')
            ->where('status', 'pending')
            ->count();

        $monthlyReservationTotal = Reservation::query()
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->count();
        $monthlyFacilityUsageCount = Reservation::query()
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->distinct('facility_id')
            ->count('facility_id');
        $monthlyUtilization = [
            'period_label' => $startOfMonth->format('F Y'),
            'reservation_total' => $monthlyReservationTotal,
            'facility_usage_count' => $monthlyFacilityUsageCount,
        ];

        return compact(
            'topFacilities',
            'peakDays',
            'revenue',
            'damageCount',
            'damageChargeCount',
            'pendingDamagePayments',
            'monthlyUtilization'
        );
    }

    private function assertFeature(string $feature): void
    {
        if (! Pricing::enforcementEnabled()) {
            return;
        }

        abort_unless(
            Pricing::allows($feature),
            403,
            'Report export is available on Premium plans.'
        );
    }

    /**
     * @return array<int, array<int, string|int|float>>
     */
    private function exportRows(array $data, string $generatedBy): array
    {
        $rows = [
            ['Generated By', $generatedBy],
            ['Generated At', now()->toDateTimeString()],
            ['Monthly Utilization Period', $data['monthlyUtilization']['period_label'] ?? ''],
            ['Monthly Reservations', (int) ($data['monthlyUtilization']['reservation_total'] ?? 0)],
            ['Monthly Facilities Used', (int) ($data['monthlyUtilization']['facility_usage_count'] ?? 0)],
            ['Revenue', (float) ($data['revenue'] ?? 0)],
            ['Damage Reports', (int) ($data['damageCount'] ?? 0)],
            ['Damage Charges', (int) ($data['damageChargeCount'] ?? 0)],
            ['Pending Damage Payments', (int) ($data['pendingDamagePayments'] ?? 0)],
            [],
            ['Top Facilities'],
            ['Facility', 'Reservations'],
        ];

        foreach ($data['topFacilities'] ?? [] as $facility) {
            $rows[] = [$facility->name, (int) $facility->reservations_count];
        }

        $rows[] = [];
        $rows[] = ['Peak Days'];
        $rows[] = ['Day', 'Reservations'];
        foreach ($data['peakDays'] ?? [] as $day) {
            $rows[] = [$day->day_name, (int) $day->total];
        }

        return $rows;
    }
}
