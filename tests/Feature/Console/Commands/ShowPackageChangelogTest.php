<?php

use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;

it('shows changelogs for a package', function () {
    $package = Package::factory()->create();
    $changelog = Changelog::factory()->for($package)->create([
        'old_version' => '1.0.0',
        'new_version' => '2.0.0',
        'status' => ChangelogStatus::Checked,
        'title' => 'Version 2.0.0',
        'summary' => 'A major update.',
    ]);
    ChangelogChange::factory()->for($changelog)->asBreaking()->create([
        'title' => 'Drop old PHP',
        'message' => 'PHP 8.2 is now required.',
    ]);

    $this->artisan('package:changelog', [
        'package' => $package->name,
    ])
        ->assertSuccessful();
});

it('filters changelogs by status', function () {
    $package = Package::factory()->create();
    Changelog::factory()->for($package)->checked()->create();
    Changelog::factory()->for($package)->create(['status' => ChangelogStatus::Backlog]);

    $this->artisan('package:changelog', [
        'package' => $package->name,
        '--status' => 'checked',
    ])
        ->assertSuccessful();

    expect($package->changelogs()->where('status', 'checked')->count())->toBe(1);
});

it('fails when the package does not exist', function () {
    $this->artisan('package:changelog', [
        'package' => 'missing/package',
    ])
        ->assertFailed()
        ->expectsPromptsError('No package found with the given identifier.');
});

it('limits the number of changelogs', function () {
    $package = Package::factory()->create();
    Changelog::factory()->for($package)->count(5)->create();

    $this->artisan('package:changelog', [
        'package' => $package->name,
        '--limit' => 2,
    ])
        ->assertSuccessful();
});
