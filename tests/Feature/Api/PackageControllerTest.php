<?php

use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

describe('index', function (): void {
    beforeEach(function (): void {
        Package::factory()->create(['name' => 'laravel/framework', 'current_version' => '11.0.0']);
        Package::factory()->create(['name' => 'laravel/ai', 'current_version' => '0.11.0']);
        Package::factory()->create(['name' => 'roots/wordpress', 'current_version' => '6.0.0']);
    });

    it('returns a paginated list of active packages ordered by name', function (): void {
        $response = getJson(route('api.packages.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'laravel/ai')
            ->assertJsonPath('data.1.name', 'laravel/framework')
            ->assertJsonPath('data.2.name', 'roots/wordpress')
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 3)->has('links')->has('meta'));
    });

    it('filters packages by vendor', function (): void {
        $response = getJson(route('api.packages.index', ['vendor' => 'laravel']));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'laravel/ai')
            ->assertJsonPath('data.1.name', 'laravel/framework')
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 2)->has('links')->has('meta'));
    });

    it('filters packages by search query', function (): void {
        $response = getJson(route('api.packages.index', ['q' => 'framework']));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'laravel/framework')
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->has('links')->has('meta'));
    });

    it('excludes inactive packages', function (): void {
        Package::factory()->create(['name' => 'inactive/package', 'is_active' => false]);

        $response = getJson(route('api.packages.index'));

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 3)->has('links')->has('meta'));
    });

    it('includes the latest checked changelog for each package', function (): void {
        $package = Package::query()->where('name', 'laravel/framework')->sole();

        Changelog::factory()->checked()->create([
            'package_id' => $package->id,
            'old_version' => '10.0.0',
            'new_version' => '11.0.0',
            'title' => 'Laravel 11',
        ]);

        $response = getJson(route('api.packages.index'));

        $response->assertOk()
            ->assertJsonPath('data.1.name', 'laravel/framework')
            ->assertJsonPath('data.1.latest_changelog.new_version', '11.0.0')
            ->assertJsonPath('data.1.latest_changelog.title', 'Laravel 11');
    });
});

describe('show', function (): void {
    it('returns a package with its checked changelogs', function (): void {
        $package = Package::factory()->create(['name' => 'laravel/framework']);

        $changelog = Changelog::factory()->checked()->create([
            'package_id' => $package->id,
            'old_version' => '10.0.0',
            'new_version' => '11.0.0',
            'title' => 'Laravel 11',
        ]);

        $response = getJson(route('api.packages.show', ['vendor' => 'laravel', 'name' => 'framework']));

        $response->assertOk()
            ->assertJsonPath('data.name', 'laravel/framework')
            ->assertJsonPath('data.changelogs.0.new_version', '11.0.0')
            ->assertJsonPath('data.changelogs.0.title', 'Laravel 11');
    });

    it('returns 404 for an inactive package', function (): void {
        Package::factory()->create(['name' => 'hidden/package', 'is_active' => false]);

        $response = getJson(route('api.packages.show', ['vendor' => 'hidden', 'name' => 'package']));

        $response->assertNotFound();
    });

    it('returns 404 for an unknown package', function (): void {
        $response = getJson(route('api.packages.show', ['vendor' => 'unknown', 'name' => 'package']));

        $response->assertNotFound();
    });
});
