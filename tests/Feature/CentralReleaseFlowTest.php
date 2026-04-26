<?php

use App\Http\Middleware\EnsureCentralHost;
use App\Models\CentralUser;
use App\Models\Release;
use App\Services\CentralReleaseService;
use App\Services\GlobalUpdateService;

it('allows super admin to open central releases page', function () {
    $this->withoutMiddleware(EnsureCentralHost::class);

    $superAdmin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $response = $this
        ->actingAs($superAdmin, 'web')
        ->get('/central/releases');

    $response->assertOk();
});

it('allows super admin to detect approve and save a new feature release', function () {
    $this->withoutMiddleware(EnsureCentralHost::class);

    $superAdmin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    // Step 1: mock detect/sync result from GitHub
    $detectMock = \Mockery::mock(CentralReleaseService::class);
    $detectMock->shouldReceive('syncLatestChanges')
        ->once()
        ->andReturn([
            'release_id' => 9999,
            'is_new' => true,
            'suggested_version' => 'v2.0.0',
            'changes_detected' => ['feature'],
            'files_affected' => ['app/Http/Controllers/Central/ReleaseController.php'],
            'release_notes' => 'Central release flow test feature',
            'risk_level' => 'medium',
        ]);

    $this->app->instance(CentralReleaseService::class, $detectMock);

    $detectResponse = $this
        ->actingAs($superAdmin, 'web')
        ->post('/central/releases/detect-and-store');

    $detectResponse->assertStatus(302);
    $detectResponse->assertSessionHas('success');

    // Step 2: create a detected release and approve it
    $release = Release::query()->create([
        'title' => 'Central Release Feature Test',
        'version' => null,
        'suggested_version' => 'v2.0.0',
        'release_type' => 'feature',
        'notes' => 'Detected feature update for testing',
        'status' => 'detected',
        'changes_detected' => ['feature'],
        'files_affected' => ['resources/views/central/releases/index.blade.php'],
        'risk_level' => 'low',
        'source_commit_sha' => 'test123commitsha',
    ]);

    // Rebind service for approve/save flow
    $releaseServiceMock = \Mockery::mock(CentralReleaseService::class);

    $releaseServiceMock->shouldReceive('approve')
        ->once()
        ->withArgs(function (Release $approvedRelease, int $actorId) use ($release, $superAdmin) {
            return $approvedRelease->id === $release->id && $actorId === (int) $superAdmin->id;
        })
        ->andReturnUsing(function (Release $approvedRelease, int $actorId) {
            $approvedRelease->status = 'approved';
            $approvedRelease->approved_by = $actorId;
            $approvedRelease->save();
        });

    $releaseServiceMock->shouldReceive('updateReleaseDetails')
        ->once()
        ->withArgs(function (Release $updatedRelease, string $releaseType, string $notes) use ($release) {
            return $updatedRelease->id === $release->id
                && $releaseType === 'feature'
                && str_contains($notes, 'Joshua Cagaanan Test NOTE')
                && str_contains($notes, 'Browser Link: /tenant/feature-preview');
        });

    $releaseServiceMock->shouldReceive('markPublished')
        ->once()
        ->withArgs(function (Release $publishedRelease, string $version) use ($release) {
            return $publishedRelease->id === $release->id && $version === 'v2.0.0';
        });

    $this->app->instance(CentralReleaseService::class, $releaseServiceMock);

    $approveResponse = $this
        ->actingAs($superAdmin, 'web')
        ->post("/central/releases/{$release->id}/approve");

    $approveResponse->assertStatus(302);
    $approveResponse->assertSessionHas('success');

    // Step 3: mock global publish and save system version
    $globalUpdateMock = \Mockery::mock(GlobalUpdateService::class);
    $globalUpdateMock->shouldReceive('publishUpdate')
        ->once()
        ->andReturn([
            'success' => true,
            'version' => 'v2.0.0',
            'github_release_id' => 20001,
        ]);

    $this->app->instance(GlobalUpdateService::class, $globalUpdateMock);

    $saveResponse = $this
        ->actingAs($superAdmin, 'web')
        ->post("/central/releases/{$release->id}/save-version", [
            'version' => 'v2.0.0',
            'release_type' => 'feature',
            'notes' => 'Joshua Cagaanan Test NOTE - Added central feature release flow test.',
            'browser_link' => '/tenant/feature-preview',
        ]);

    $saveResponse->assertStatus(302);
    $saveResponse->assertSessionHas('success');
});
