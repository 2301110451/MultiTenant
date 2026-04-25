<?php

use App\Http\Middleware\EnsureCentralHost;
use App\Models\CentralUser;
use App\Models\SystemVersion;
use Illuminate\Support\Facades\Http;

it('logs a new system version from admin without publishing to github', function () {
    $admin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin, 'web')
        ->withoutMiddleware(EnsureCentralHost::class)
        ->post(route('central.system-versions.store'), [
            'version' => 'v1.2.0',
            'release_type' => 'feature',
            'notes' => 'Added admin dashboard widget',
            'migration_batch' => '2026_04_admin_feature',
            'publish_to_github' => false,
        ])
        ->assertRedirect(route('central.system-versions.index'));

    expect(SystemVersion::query()->where('version', 'v1.2.0')->exists())->toBeTrue();
});

it('logs version and publishes release/tag to github when enabled', function () {
    config()->set('services.github.token', 'fake-token');
    config()->set('services.github.owner', 'your-owner');
    config()->set('services.github.repo', 'your-repo');

    Http::fake([
        'https://api.github.com/repos/your-owner/your-repo/releases' => Http::response([
            'id' => 999001,
            'tag_name' => 'v1.3.0',
            'name' => 'Release v1.3.0',
        ], 201),
    ]);

    $admin = CentralUser::factory()->create([
        'is_super_admin' => true,
    ]);

    $this->actingAs($admin, 'web')
        ->withoutMiddleware(EnsureCentralHost::class)
        ->post(route('central.system-versions.store'), [
            'version' => 'v1.3.0',
            'release_type' => 'feature',
            'notes' => 'Added admin feature toggle panel',
            'publish_to_github' => true,
            'github_title' => 'Release v1.3.0',
            'github_notes' => 'Added admin feature toggle panel',
        ])
        ->assertRedirect(route('central.system-versions.index'));

    expect(SystemVersion::query()->where('version', 'v1.3.0')->exists())->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.github.com/repos/your-owner/your-repo/releases'
            && $request['tag_name'] === 'v1.3.0'
            && $request['name'] === 'Release v1.3.0';
    });
});
