<?php

namespace Database\Seeders;

use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;
use App\Models\Registry;
use Illuminate\Database\Seeder;

class MockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packagist = Registry::create([
            'name' => 'Packagist',
            'url' => 'https://repo.packagist.org',
            'auth_type' => 'none',
        ]);

        Registry::create([
            'name' => 'WPackagist',
            'url' => 'https://wpackagist.org',
            'auth_type' => 'none',
        ]);

        Package::create([
            'name' => 'laravel/laravel',
            'registry_id' => $packagist->id,
            'is_active' => true,
            'current_version' => '13.0.0',
        ]);

        Package::create([
            'name' => 'laravel/framework',
            'registry_id' => $packagist->id,
            'is_active' => true,
            'current_version' => '13.0.0',
        ]);

        Changelog::factory(5)->create([
            'package_id' => 1,
            'status' => ChangelogStatus::Checked,
        ]);

        ChangelogChange::factory(50)->create([
            'changelog_id' => 1,
        ]);

    }
}
