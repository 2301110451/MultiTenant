<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_user_management_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin', // adjust to your actual column/value
        ]);

        $response = $this->actingAs($superAdmin)->get('/super-admin/users'); // adjust route

        $response->assertStatus(200);
        $response->assertSee('User Management'); // adjust text on your blade page
    }

    public function test_non_super_admin_cannot_access_user_management_page(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'user', // adjust to your actual value
        ]);

        $response = $this->actingAs($regularUser)->get('/super-admin/users'); // adjust route

        // Use whichever your app does: 403 OR redirect
        $response->assertStatus(403);
        // or: $response->assertRedirect('/home');
    }
}
