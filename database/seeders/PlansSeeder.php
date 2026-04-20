<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'monthly_reservation_limit' => 100,
                'features' => [
                    'reports' => true,
                    'simple_reporting_dashboard' => true,
                    'basic_calendar_view' => true,
                    'manual_approval_workflow' => true,
                    'online_request_approval' => false,
                    'qr_checkin' => false,
                    'integrated_payments' => false,
                    'damage_penalty_tracking' => false,
                    'monthly_utilization_reports' => false,
                    'advanced_analytics_dashboard' => false,
                    'export_reports_pdf' => false,
                    'export_reports_csv' => false,
                    'export_reports_excel' => false,
                    'priority_support' => false,
                    'auto_availability_blocking' => false,
                ],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'monthly_reservation_limit' => 1000,
                'features' => [
                    'reports' => true,
                    'simple_reporting_dashboard' => true,
                    'basic_calendar_view' => true,
                    'manual_approval_workflow' => true,
                    'online_request_approval' => true,
                    'qr_checkin' => false,
                    'integrated_payments' => false,
                    'damage_penalty_tracking' => true,
                    'monthly_utilization_reports' => true,
                    'advanced_analytics_dashboard' => false,
                    'export_reports_pdf' => false,
                    'export_reports_csv' => false,
                    'export_reports_excel' => false,
                    'priority_support' => false,
                    'auto_availability_blocking' => true,
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'monthly_reservation_limit' => null,
                'features' => [
                    'reports' => true,
                    'simple_reporting_dashboard' => true,
                    'basic_calendar_view' => true,
                    'manual_approval_workflow' => true,
                    'online_request_approval' => true,
                    'qr_checkin' => true,
                    'integrated_payments' => true,
                    'damage_penalty_tracking' => true,
                    'monthly_utilization_reports' => true,
                    'advanced_analytics_dashboard' => true,
                    'export_reports_pdf' => true,
                    'export_reports_csv' => true,
                    'export_reports_excel' => true,
                    'priority_support' => true,
                    'auto_availability_blocking' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
