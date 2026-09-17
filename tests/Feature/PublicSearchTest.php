<?php

use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;

use function Pest\Laravel\get;

beforeEach(function (): void {
    Package::factory()->create(['name' => 'laravel/framework', 'current_version' => '11.0.0']);
    Package::factory()->create(['name' => 'laravel/ai', 'current_version' => '0.11.0']);
    Package::factory()->create(['name' => 'roots/wordpress', 'current_version' => '6.0.0']);
});

test('the public search page loads without authentication', function (): void {
    $response = get(route('home'));

    $response->assertOk();
});

test('the page lists active packages', function (): void {
    $response = get(route('home'));

    $response->assertSee('laravel/framework');
    $response->assertSee('laravel/ai');
    $response->assertSee('roots/wordpress');
});

test('the page filters packages by author', function (): void {
    $response = get(route('home', ['vendor' => 'laravel']));

    $response->assertSee('laravel/framework');
    $response->assertSee('laravel/ai');
    $response->assertDontSee('roots/wordpress');
});

test('the page filters packages by search query', function (): void {
    $response = get(route('home', ['q' => 'framework']));

    $response->assertSee('laravel/framework');
    $response->assertDontSee('roots/wordpress');
});

test('the public package page shows its changelogs', function (): void {
    $package = Package::query()->where('name', 'laravel/framework')->first();

    Changelog::factory()->create([
        'package_id' => $package->id,
        'old_version' => '10.0.0',
        'new_version' => '11.0.0',
        'status' => ChangelogStatus::Checked,
        'title' => 'Laravel 11',
        'summary' => 'Major release.',
    ]);

    $response = get(route('packages.show', ['vendor' => 'laravel', 'name' => 'framework']));

    $response->assertOk();
    $response->assertSee('laravel/framework');
    $response->assertSee('Laravel 11');
    $response->assertSee('10.0.0 → 11.0.0');
});

test('the public changelog page shows change details', function (): void {
    $package = Package::query()->where('name', 'laravel/framework')->first();

    $changelog = Changelog::factory()->create([
        'package_id' => $package->id,
        'old_version' => '10.0.0',
        'new_version' => '11.0.0',
        'status' => ChangelogStatus::Checked,
        'title' => 'Laravel 11',
        'summary' => 'Major release.',
    ]);

    ChangelogChange::factory()->asBreaking()->create([
        'changelog_id' => $changelog->id,
        'title' => 'Drops PHP 8.1 support',
    ]);

    ChangelogChange::factory()->asNew()->create([
        'changelog_id' => $changelog->id,
        'title' => 'New configuration layer',
    ]);

    $response = get(route('changelogs.show', [
        'vendor' => 'laravel',
        'name' => 'framework',
        'new_version' => $changelog->new_version,
    ]));

    $response->assertOk();
    $response->assertSee('Laravel 11');
    $response->assertSee('10.0.0 → 11.0.0');
    $response->assertSee('Drops PHP 8.1 support');
    $response->assertSee('New configuration layer');
    $response->assertSee('Breaking changes');
    $response->assertSee('New');
});
