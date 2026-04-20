<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'monthly_reservation_limit' => 100,
                'features' => json_encode([
                    'manual_approval_workflow' => true,
                    'online_request_approval' => false,
                    'basic_calendar_view' => true,
                    'simple_reporting_dashboard' => true,
                    'reports' => true,
                    'monthly_utilization_reports' => false,
                    'advanced_analytics_dashboard' => false,
                    'auto_availability_blocking' => false,
                    'damage_penalty_tracking' => false,
                    'integrated_payments' => false,
                    'priority_support' => false,
                    'export_reports_pdf' => false,
                    'export_reports_csv' => false,
                    'export_reports_excel' => false,
                    'qr_checkin' => false,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'monthly_reservation_limit' => 1000,
                'features' => json_encode([
                    'manual_approval_workflow' => true,
                    'online_request_approval' => true,
                    'basic_calendar_view' => true,
                    'simple_reporting_dashboard' => true,
                    'reports' => true,
                    'monthly_utilization_reports' => true,
                    'advanced_analytics_dashboard' => false,
                    'auto_availability_blocking' => true,
                    'damage_penalty_tracking' => true,
                    'integrated_payments' => false,
                    'priority_support' => false,
                    'export_reports_pdf' => false,
                    'export_reports_csv' => false,
                    'export_reports_excel' => false,
                    'qr_checkin' => false,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'monthly_reservation_limit' => null,
                'features' => json_encode([
                    'manual_approval_workflow' => true,
                    'online_request_approval' => true,
                    'basic_calendar_view' => true,
                    'simple_reporting_dashboard' => true,
                    'reports' => true,
                    'monthly_utilization_reports' => true,
                    'advanced_analytics_dashboard' => true,
                    'auto_availability_blocking' => true,
                    'damage_penalty_tracking' => true,
                    'integrated_payments' => true,
                    'priority_support' => true,
                    'export_reports_pdf' => true,
                    'export_reports_csv' => true,
                    'export_reports_excel' => true,
                    'qr_checkin' => true,
                ], JSON_THROW_ON_ERROR),
            ],
        ];

        foreach ($rows as $row) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        // Keep plan rows to avoid disconnecting tenants from existing subscriptions.
    }
};
