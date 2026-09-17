<?php

use App\Jobs\GenerateChangelog;
use App\Models\Package;
use Illuminate\Support\Facades\Bus;

it('dispatches the changelog generation job for a package by name', function () {
    Bus::fake();

    $package = Package::factory()->create(['name' => 'test/package', 'current_version' => '1.0.0']);

    $this->artisan('package:compare', [
        'package' => $package->name,
        'old-version' => '1.0.0',
        'new-version' => '2.0.0',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Changelog generation dispatched');

    Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package) {
        return $job->package->is($package)
            && $job->oldVersion === '1.0.0'
            && $job->newVersion === '2.0.0';
    });
});

it('dispatches the changelog generation job for a package by id', function () {
    Bus::fake();

    $package = Package::factory()->create(['name' => 'test/package', 'current_version' => '1.0.0']);

    $this->artisan('package:compare', [
        'package' => (string) $package->id,
        'old-version' => '1.0.0',
        'new-version' => '2.0.0',
    ])->assertSuccessful();

    Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package) {
        return $job->package->is($package);
    });
});

it('fails when the package is unknown', function () {
    $this->artisan('package:compare', [
        'package' => 'missing/package',
        'old-version' => '1.0.0',
        'new-version' => '2.0.0',
    ])
        ->assertFailed()
        ->expectsOutputToContain('No package found');
});
