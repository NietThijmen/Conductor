<?php

use App\Models\Package;
use App\Models\Registry;

it('lists packages', function () {
    Package::factory()->count(3)->create();

    $this->artisan('package:manage', ['action' => 'list'])
        ->assertSuccessful();
});

it('creates a package', function () {
    $registry = Registry::factory()->create();

    $this->artisan('package:manage', [
        'action' => 'create',
        '--name' => 'laravel/framework',
        '--registry-id' => $registry->id,
        '--current-version' => '10.0.0',
        '--active' => 'yes',
    ])
        ->assertSuccessful();

    $package = Package::where('name', 'laravel/framework')->first();

    expect($package)->not->toBeNull()
        ->and($package->registry_id)->toBe($registry->id)
        ->and($package->current_version)->toBe('10.0.0')
        ->and($package->is_active)->toBeTrue();
});

it('creates a package without a registry', function () {
    $this->artisan('package:manage', [
        'action' => 'create',
        '--name' => 'laravel/framework',
        '--current-version' => '10.0.0',
        '--active' => 'yes',
    ])
        ->assertSuccessful();

    expect(Package::where('name', 'laravel/framework')->value('registry_id'))->toBeNull();
});

it('updates a package', function () {
    $package = Package::factory()->create(['current_version' => '1.0.0']);

    $this->artisan('package:manage', [
        'action' => 'update',
        '--id' => $package->id,
        '--name' => $package->name,
        '--current-version' => '2.0.0',
        '--active' => 'no',
    ])
        ->assertSuccessful();

    $package->refresh();

    expect($package->current_version)->toBe('2.0.0')
        ->and($package->is_active)->toBeFalse();
});

it('shows a package', function () {
    $package = Package::factory()->create();

    $this->artisan('package:manage', [
        'action' => 'show',
        '--id' => $package->id,
    ])
        ->assertSuccessful();
});

it('deletes a package when forced', function () {
    $package = Package::factory()->create();

    $this->artisan('package:manage', [
        'action' => 'delete',
        '--id' => $package->id,
        '--force' => true,
    ])
        ->assertSuccessful();

    expect(Package::find($package->id))->toBeNull();
});
