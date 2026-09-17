<?php

use App\Enums\RegistryAuthType;
use App\Models\Package;
use App\Models\Registry;

it('lists registries', function () {
    Registry::factory()->count(3)->create();

    $this->artisan('registry:manage', ['action' => 'list'])
        ->assertSuccessful();
});

it('creates a registry', function () {
    $this->artisan('registry:manage', [
        'action' => 'create',
        '--name' => 'Packagist',
        '--url' => 'https://packagist.org',
        '--auth-type' => 'token',
        '--auth-token' => 'secret-token',
        '--active' => 'yes',
    ])
        ->assertSuccessful();

    $registry = Registry::where('name', 'Packagist')->first();

    expect($registry)->not->toBeNull()
        ->and($registry->url)->toBe('https://packagist.org')
        ->and($registry->auth_type)->toBe(RegistryAuthType::Token)
        ->and($registry->auth_config)->toBe(['token' => 'secret-token'])
        ->and($registry->is_active)->toBeTrue();
});

it('updates a registry', function () {
    $registry = Registry::factory()->create(['url' => 'https://old.example.com']);

    $this->artisan('registry:manage', [
        'action' => 'update',
        '--id' => $registry->id,
        '--name' => $registry->name,
        '--url' => 'https://new.example.com',
        '--active' => 'no',
    ])
        ->assertSuccessful();

    $registry->refresh();

    expect($registry->url)->toBe('https://new.example.com')
        ->and($registry->is_active)->toBeFalse();
});

it('shows a registry', function () {
    $registry = Registry::factory()->create();

    $this->artisan('registry:manage', [
        'action' => 'show',
        '--id' => $registry->id,
    ])
        ->assertSuccessful();
});

it('deletes a registry when forced', function () {
    $registry = Registry::factory()->create();

    $this->artisan('registry:manage', [
        'action' => 'delete',
        '--id' => $registry->id,
        '--force' => true,
    ])
        ->assertSuccessful();

    expect(Registry::find($registry->id))->toBeNull();
});

it('prevents deleting a registry with packages', function () {
    $registry = Registry::factory()->has(Package::factory()->count(1))->create();

    $this->artisan('registry:manage', [
        'action' => 'delete',
        '--id' => $registry->id,
        '--force' => true,
    ])
        ->assertFailed();
});
