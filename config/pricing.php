<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pricing Enforcement
    |--------------------------------------------------------------------------
    |
    | Toggle enforcement globally for safe rollout. Keep enabled in production.
    |
    */
    'enforcement_enabled' => (bool) env('PRICING_ENFORCEMENT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Canonical Feature Matrix
    |--------------------------------------------------------------------------
    |
    | These defaults are merged with persisted plan JSON from the database.
    | Database values can override specific booleans as needed.
    |
    */
    'tiers' => [
        'basic' => [
            'monthly_reservation_limit' => 100,
            'features' => [
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
            ],
        ],
        'standard' => [
            'monthly_reservation_limit' => 1000,
            'features' => [
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
            ],
        ],
        'premium' => [
            'monthly_reservation_limit' => null,
            'features' => [
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
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Integrations (Premium-ready)
    |--------------------------------------------------------------------------
    */
    'payments' => [
        'provider' => env('PRICING_PAYMENTS_PROVIDER', 'stripe'),
        'paypal_enabled' => (bool) env('PRICING_PAYPAL_ENABLED', false),
    ],

    'support' => [
        'priority_email' => env('PRICING_PRIORITY_SUPPORT_EMAIL'),
    ],
];
