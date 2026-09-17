<?php

use App\Enums\ChangelogChangeType;
use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

describe('index', function (): void {
    it('returns the checked changelogs for a package', function (): void {
        $package = Package::factory()->create(['name' => 'laravel/framework']);

        Changelog::factory()->checked()->create([
            'package_id' => $package->id,
            'old_version' => '10.0.0',
            'new_version' => '11.0.0',
            'title' => 'Laravel 11',
        ]);

        Changelog::factory()->create([
            'package_id' => $package->id,
            'status' => ChangelogStatus::Backlog,
        ]);

        $response = getJson(route('api.packages.changelogs.index', ['vendor' => 'laravel', 'name' => 'framework']));

        $response->assertOk()
            ->assertJsonPath('data.0.new_version', '11.0.0')
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->has('links')->has('meta'));
    });

    it('returns 404 when the package is inactive', function (): void {
        Package::factory()->create(['name' => 'hidden/package', 'is_active' => false]);

        $response = getJson(route('api.packages.changelogs.index', ['vendor' => 'hidden', 'name' => 'package']));

        $response->assertNotFound();
    });
});

describe('show', function (): void {
    it('returns the changelog items for a specific version', function (): void {
        $package = Package::factory()->create(['name' => 'laravel/framework']);

        $changelog = Changelog::factory()->checked()->create([
            'package_id' => $package->id,
            'old_version' => '10.0.0',
            'new_version' => '11.0.0',
            'title' => 'Laravel 11',
        ]);

        ChangelogChange::factory()->asBreaking()->create([
            'changelog_id' => $changelog->id,
            'title' => 'Drops PHP 8.1 support',
        ]);

        ChangelogChange::factory()->asNew()->create([
            'changelog_id' => $changelog->id,
            'title' => 'New configuration layer',
        ]);

        $response = getJson(route('api.packages.changelogs.show', [
            'vendor' => 'laravel',
            'name' => 'framework',
            'new_version' => '11.0.0',
        ]));

        $response->assertOk()
            ->assertJsonPath('data.new_version', '11.0.0')
            ->assertJsonPath('data.title', 'Laravel 11')
            ->assertJsonPath('data.package.name', 'laravel/framework')
            ->assertJsonPath('data.changes.0.title', 'Drops PHP 8.1 support')
            ->assertJsonPath('data.changes.0.type', ChangelogChangeType::Breaking->value)
            ->assertJsonPath('data.changes.1.title', 'New configuration layer')
            ->assertJsonPath('data.changes.1.type', ChangelogChangeType::New->value);
    });

    it('returns 404 for an unchecked changelog version', function (): void {
        $package = Package::factory()->create(['name' => 'laravel/framework']);

        Changelog::factory()->create([
            'package_id' => $package->id,
            'new_version' => '11.0.0',
            'status' => ChangelogStatus::Backlog,
        ]);

        $response = getJson(route('api.packages.changelogs.show', [
            'vendor' => 'laravel',
            'name' => 'framework',
            'new_version' => '11.0.0',
        ]));

        $response->assertNotFound();
    });

    it('returns 404 for an unknown version', function (): void {
        $package = Package::factory()->create(['name' => 'laravel/framework']);

        Changelog::factory()->checked()->create([
            'package_id' => $package->id,
            'new_version' => '11.0.0',
        ]);

        $response = getJson(route('api.packages.changelogs.show', [
            'vendor' => 'laravel',
            'name' => 'framework',
            'new_version' => '12.0.0',
        ]));

        $response->assertNotFound();
    });
});
