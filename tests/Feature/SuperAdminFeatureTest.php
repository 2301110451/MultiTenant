<?php

use App\Http\Middleware\EnsureCentralHost;
use App\Models\CentralUser;
use App\Models\Release;
use App\Services\CentralReleaseService;
use Mockery;

it('allows super admin to sync new release from github', function () {
    $this->withoutMiddleware(EnsureCentralHost::class);

    $superAdmin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $mock = Mockery::mock(CentralReleaseService::class);
    $mock->shouldReceive('syncLatestChanges')
        ->once()
        ->andReturn([
            'release_id' => 999,
            'is_new' => true,
            'suggested_version' => 'v1.2.0',
            'changes_detected' => ['feature'],
            'files_affected' => ['app/Http/Controllers/NewFeatureController.php'],
            'release_notes' => 'Detected new feature from latest commit',
            'risk_level' => 'medium',
        ]);

    $this->app->instance(CentralReleaseService::class, $mock);

    $response = $this
        ->actingAs($superAdmin, 'web')
        ->post('/central/releases/detect-and-store');

    $response->assertStatus(302);
    $response->assertSessionHas('success');
});

it('allows super admin to approve detected release in central releases', function () {
    $this->withoutMiddleware(EnsureCentralHost::class);

    $superAdmin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $release = Release::query()->create([
        'title' => 'New feature release',
        'version' => null,
        'suggested_version' => 'v1.2.0',
        'release_type' => 'FEATURE',
        'notes' => 'Initial detected release',
        'status' => 'detected',
        'changes_detected' => ['feature'],
        'files_affected' => ['app/Services/NewFeatureService.php'],
        'risk_level' => 'medium',
        'source_commit_sha' => 'abc123def456',
    ]);

    $response = $this
        ->actingAs($superAdmin, 'web')
        ->post("/central/releases/{$release->id}/approve");

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $release->refresh();
    expect($release->status)->toBe('approved');
    expect((int) $release->approved_by)->toBe((int) $superAdmin->id);
});
