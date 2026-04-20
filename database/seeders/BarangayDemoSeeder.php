<?php

namespace Database\Seeders;

use App\Enums\FacilityKind;
use App\Enums\TenantRole;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarangayDemoSeeder extends Seeder
{
    public function run(): void
    {
        /** @var TenantProvisioningService $provisioning */
        $provisioning = app(TenantProvisioningService::class);

        $planPremium = Plan::query()->where('slug', 'premium')->first();
        $planStandard = Plan::query()->where('slug', 'standard')->first();

        $carmen = $this->ensureTenant(
            $provisioning,
            'Barangay Carmen',
            'carmen.localhost',
            $planPremium?->id
        );

        $macasandig = $this->ensureTenant(
            $provisioning,
            'Barangay Macasandig',
            'macasandig.localhost',
            $planStandard?->id
        );

        $this->seedTenantContent($carmen, 'carmen');
        $this->seedTenantContent($macasandig, 'macasandig');
    }

    private function ensureTenant(
        TenantProvisioningService $provisioning,
        string $name,
        string $domain,
        ?int $planId,
    ): Tenant {
        $existing = Tenant::query()->where('name', $name)->first();
        if ($existing) {
            return $existing;
        }

        return $provisioning->provisionTenant($name, $domain, $planId);
    }

    private function seedTenantContent(Tenant $tenant, string $slug): void
    {
        $tenant->configureTenantConnection();

        if (User::query()->exists()) {
            return;
        }

        $users = [
            ['name' => 'Maria Santos', 'email' => 'admin@'.$slug.'.test', 'role' => TenantRole::TenantAdmin],
            ['name' => 'Pedro Reyes', 'email' => 'staff@'.$slug.'.test', 'role' => TenantRole::Staff],
            ['name' => 'Ana Cruz', 'email' => 'viewer@'.$slug.'.test', 'role' => TenantRole::Viewer],
            ['name' => 'Juan Dela Cruz', 'email' => 'resident@'.$slug.'.test', 'role' => TenantRole::Resident],
        ];

        foreach ($users as $u) {
            User::query()->create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('password'),
                'role' => $u['role'],
                'email_verified_at' => now(),
            ]);
        }

        $facilities = [
            ['name' => 'Barangay Hall', 'capacity' => 120, 'hourly_rate' => 500, 'rules' => 'No smoking. Clean up after use.'],
            ['name' => 'Covered Court', 'capacity' => 300, 'hourly_rate' => 800, 'rules' => 'Sports shoes required on court.'],
            ['name' => 'Multi-Purpose Hall', 'capacity' => 80, 'hourly_rate' => 400, 'rules' => 'Sound system by request only.'],
        ];

        foreach ($facilities as $f) {
            Facility::query()->create([
                'name' => $f['name'],
                'kind' => FacilityKind::Facility,
                'description' => 'Managed facility for community use.',
                'capacity' => $f['capacity'],
                'rules' => $f['rules'],
                'operating_hours' => [
                    'mon' => '08:00-17:00',
                    'tue' => '08:00-17:00',
                    'wed' => '08:00-17:00',
                    'thu' => '08:00-17:00',
                    'fri' => '08:00-17:00',
                    'sat' => '08:00-12:00',
                ],
                'hourly_rate' => $f['hourly_rate'],
                'is_active' => true,
            ]);
        }

        Facility::query()->create([
            'name' => 'Portable sound package',
            'kind' => FacilityKind::Equipment,
            'description' => 'PA + 2 microphones — reserve by time slot like a facility.',
            'capacity' => 1,
            'rules' => 'Return complete set; report damage immediately.',
            'operating_hours' => [
                'mon' => '08:00-17:00',
                'tue' => '08:00-17:00',
                'wed' => '08:00-17:00',
                'thu' => '08:00-17:00',
                'fri' => '08:00-17:00',
                'sat' => '08:00-12:00',
            ],
            'hourly_rate' => 350,
            'is_active' => true,
        ]);

        $equipment = [
            ['name' => 'Sound system', 'description' => 'Portable PA', 'quantity_total' => 3, 'quantity_available' => 3, 'penalty_per_day' => 150],
            ['name' => 'Plastic chairs', 'description' => 'Stackable', 'quantity_total' => 200, 'quantity_available' => 200, 'penalty_per_day' => 5],
            ['name' => 'Banquet tables', 'description' => 'Folding 6ft', 'quantity_total' => 40, 'quantity_available' => 40, 'penalty_per_day' => 25],
        ];

        foreach ($equipment as $e) {
            Equipment::query()->create([
                'name' => $e['name'],
                'description' => $e['description'],
                'quantity_total' => $e['quantity_total'],
                'quantity_available' => $e['quantity_available'],
                'condition_status' => 'good',
                'penalty_per_day' => $e['penalty_per_day'] ?? 25,
                'is_active' => true,
            ]);
        }
    }
}
