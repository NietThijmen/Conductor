<?php

use App\Enums\ChangelogChangeType;
use App\Enums\ChangelogStatus;
use App\Enums\RegistryAuthType;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;
use App\Models\Registry;

test('a registry can store optional authentication', function () {
    $registry = Registry::factory()->create([
        'auth_type' => RegistryAuthType::Token,
        'auth_config' => ['token' => 'secret-token'],
    ]);

    expect($registry->auth_type)->toBe(RegistryAuthType::Token)
        ->and($registry->auth_config)->toBe(['token' => 'secret-token']);
});

test('a package can exist without a registry', function () {
    $package = Package::factory()->withoutRegistry()->create();

    expect($package->registry)->toBeNull()
        ->and($package->registry_id)->toBeNull();
});

test('a package can be linked to a registry', function () {
    $registry = Registry::factory()->create();
    $package = Package::factory()->create(['registry_id' => $registry->id]);

    expect($package->registry->is($registry))->toBeTrue();
    expect($registry->packages)->toHaveCount(1);
});

test('a changelog tracks old and new versions with a status', function () {
    $changelog = Changelog::factory()->create([
        'old_version' => '1.0.0',
        'new_version' => '2.0.0',
        'status' => ChangelogStatus::Checking,
    ]);

    expect($changelog->old_version)->toBe('1.0.0')
        ->and($changelog->new_version)->toBe('2.0.0')
        ->and($changelog->status)->toBe(ChangelogStatus::Checking);
});

test('a changelog belongs to a package and can have changes', function () {
    $changelog = Changelog::factory()
        ->has(ChangelogChange::factory()->count(3), 'changes')
        ->create();

    expect($changelog->package)->toBeInstanceOf(Package::class)
        ->and($changelog->changes)->toHaveCount(3);
});

test('a changelog change has a type title and message', function () {
    $change = ChangelogChange::factory()->create([
        'type' => ChangelogChangeType::Breaking,
        'title' => 'Removed legacy helper',
        'message' => 'The old helper is no longer available.',
    ]);

    expect($change->type)->toBe(ChangelogChangeType::Breaking)
        ->and($change->title)->toBe('Removed legacy helper')
        ->and($change->message)->toBe('The old helper is no longer available.')
        ->and($change->changelog)->toBeInstanceOf(Changelog::class);
});
