<?php

use App\Http\Middleware\EnsureCentralHost;
use App\Models\CentralUser;

it('admin can open the new feature page', function () {
    $admin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin, 'web')
        ->withoutMiddleware(EnsureCentralHost::class)
        ->get(route('central.system-versions.index')) // change to your new feature route
        ->assertOk();
});

it('admin can submit new feature form successfully', function () {
    $admin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $response = $this->actingAs($admin, 'web')
        ->withoutMiddleware(EnsureCentralHost::class)
        ->post(route('central.system-versions.store'), [ // change to your new feature save route
            'version' => 'v9.9.9',
            'release_type' => 'feature',
            'notes' => 'Smoke test for new admin feature',
        ]);

    $response->assertRedirect(route('central.system-versions.index'));
    $response->assertSessionHasNoErrors();
});
