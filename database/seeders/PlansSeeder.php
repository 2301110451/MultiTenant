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
                'monthly_reservation_limit' => 50,
                'features' => [
                    'reports' => true,
                    'qr_checkin' => false,
                    'payments' => false,
                ],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'monthly_reservation_limit' => 200,
                'features' => [
                    'reports' => true,
                    'qr_checkin' => false,
                    'payments' => true,
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'monthly_reservation_limit' => null,
                'features' => [
                    'reports' => true,
                    'qr_checkin' => true,
                    'payments' => true,
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
