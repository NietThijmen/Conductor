<?php

use App\Http\Integrations\Composer\Requests\GetPackageMetadataRequest;
use App\Jobs\GenerateChangelog;
use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Support\Facades\Bus;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    Bus::fake();
});

afterEach(function () {
    MockClient::destroyGlobal();
});

function metadataResponse(string $packageName): array
{
    return [
        'packages' => [
            $packageName => [
                [
                    'name' => $packageName,
                    'version' => 'v0.11.2',
                    'version_normalized' => '0.11.2.0',
                    'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'abc123'],
                    'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/abc123', 'type' => 'zip', 'reference' => 'abc123'],
                    'time' => '2024-03-12T00:00:00+00:00',
                    'type' => 'library',
                ],
                [
                    'name' => $packageName,
                    'version' => 'v0.11.1',
                    'version_normalized' => '0.11.1.0',
                    'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'def456'],
                    'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/def456', 'type' => 'zip', 'reference' => 'def456'],
                    'time' => '2024-03-11T00:00:00+00:00',
                    'type' => 'library',
                ],
                [
                    'name' => $packageName,
                    'version' => 'v0.11.0',
                    'version_normalized' => '0.11.0.0',
                    'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'ghi789'],
                    'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/ghi789', 'type' => 'zip', 'reference' => 'ghi789'],
                    'time' => '2024-03-10T00:00:00+00:00',
                    'type' => 'library',
                ],
                [
                    'name' => $packageName,
                    'version' => 'v0.10.3',
                    'version_normalized' => '0.10.3.0',
                    'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'jkl012'],
                    'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/jkl012', 'type' => 'zip', 'reference' => 'jkl012'],
                    'time' => '2024-03-09T00:00:00+00:00',
                    'type' => 'library',
                ],
                [
                    'name' => $packageName,
                    'version' => 'v0.10.0',
                    'version_normalized' => '0.10.0.0',
                    'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'mno345'],
                    'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/mno345', 'type' => 'zip', 'reference' => 'mno345'],
                    'time' => '2024-03-08T00:00:00+00:00',
                    'type' => 'library',
                ],
            ],
        ],
    ];
}

it('dispatches chained changelog jobs for missing versions up to the latest', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => true,
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make(metadataResponse('laravel/ai')),
    ]);

    $this->artisan('package:check-for-updates')
        ->assertSuccessful();

    Bus::assertDispatchedTimes(GenerateChangelog::class, 4);

    $expectedTransitions = [
        ['v0.10.0', 'v0.10.3'],
        ['v0.10.3', 'v0.11.0'],
        ['v0.11.0', 'v0.11.1'],
        ['v0.11.1', 'v0.11.2'],
    ];

    foreach ($expectedTransitions as [$old, $new]) {
        Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package, $old, $new) {
            return $job->package->is($package)
                && $job->oldVersion === $old
                && $job->newVersion === $new;
        });
    }

    $package->refresh();
    expect($package->current_version)->toBe('v0.11.2');
});

it('continues chaining when some transitions already exist', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => true,
    ]);

    Changelog::factory()->create([
        'package_id' => $package->id,
        'old_version' => 'v0.10.3',
        'new_version' => 'v0.11.0',
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make(metadataResponse('laravel/ai')),
    ]);

    $this->artisan('package:check-for-updates')
        ->assertSuccessful();

    Bus::assertDispatchedTimes(GenerateChangelog::class, 3);

    $expectedTransitions = [
        ['v0.10.0', 'v0.10.3'],
        ['v0.11.0', 'v0.11.1'],
        ['v0.11.1', 'v0.11.2'],
    ];

    foreach ($expectedTransitions as [$old, $new]) {
        Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package, $old, $new) {
            return $job->package->is($package)
                && $job->oldVersion === $old
                && $job->newVersion === $new;
        });
    }

    $package->refresh();
    expect($package->current_version)->toBe('v0.11.2');
});

it('skips packages without a current version', function () {
    Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => null,
        'is_active' => true,
    ]);

    $this->artisan('package:check-for-updates')
        ->assertSuccessful()
        ->expectsOutputToContain('no current version');

    Bus::assertNothingDispatched();
});

it('skips inactive packages by default', function () {
    Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => false,
    ]);

    $this->artisan('package:check-for-updates')
        ->assertSuccessful()
        ->expectsOutputToContain('No packages to check');

    Bus::assertNothingDispatched();
});

it('includes inactive packages when the all flag is used', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => false,
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make(metadataResponse('laravel/ai')),
    ]);

    $this->artisan('package:check-for-updates', ['--all' => true])
        ->assertSuccessful();

    Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package) {
        return $job->package->is($package)
            && $job->newVersion === 'v0.11.2';
    });

    $package->refresh();
    expect($package->current_version)->toBe('v0.11.2');
});

it('skips unstable releases by default', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => true,
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/ai' => [
                    [
                        'name' => 'laravel/ai',
                        'version' => '0.11.0-RC1',
                        'version_normalized' => '0.11.0.0-RC1',
                        'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'abc123'],
                        'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/abc123', 'type' => 'zip', 'reference' => 'abc123'],
                        'time' => '2024-03-12T00:00:00+00:00',
                        'type' => 'library',
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('package:check-for-updates')
        ->assertSuccessful()
        ->expectsOutputToContain('up to date');

    Bus::assertNothingDispatched();
});

it('includes unstable releases when requested', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => true,
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/ai' => [
                    [
                        'name' => 'laravel/ai',
                        'version' => '0.11.0-RC1',
                        'version_normalized' => '0.11.0.0-RC1',
                        'source' => ['url' => 'https://github.com/example/repo.git', 'type' => 'git', 'reference' => 'abc123'],
                        'dist' => ['url' => 'https://api.github.com/repos/example/repo/zipball/abc123', 'type' => 'zip', 'reference' => 'abc123'],
                        'time' => '2024-03-12T00:00:00+00:00',
                        'type' => 'library',
                    ],
                ],
            ],
        ]),
    ]);

    $this->artisan('package:check-for-updates', ['--include-unstable' => true])
        ->assertSuccessful();

    Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package) {
        return $job->package->is($package)
            && $job->newVersion === '0.11.0-RC1';
    });

    $package->refresh();
    expect($package->current_version)->toBe('0.11.0-RC1');
});

it('only reports missing transitions in dry-run mode', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/ai',
        'current_version' => 'v0.10.0',
        'is_active' => true,
    ]);

    MockClient::global([
        GetPackageMetadataRequest::class => MockResponse::make(metadataResponse('laravel/ai')),
    ]);

    $this->artisan('package:check-for-updates', ['--dry-run' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('v0.10.0 -> v0.10.3')
        ->expectsOutputToContain('would update current version to [v0.11.2]');

    Bus::assertNothingDispatched();

    $package->refresh();
    expect($package->current_version)->toBe('v0.10.0');
});

it('fails when an unknown package identifier is provided', function () {
    $this->artisan('package:check-for-updates', ['package' => 'missing/package'])
        ->assertFailed()
        ->expectsOutputToContain('No package found');
});
