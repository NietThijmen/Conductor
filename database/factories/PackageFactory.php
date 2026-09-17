<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Registry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registry_id' => Registry::factory(),
            'name' => fake()->unique()->word().'/'.fake()->word(),
            'current_version' => fake()->semver(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the package is not linked to a registry.
     */
    public function withoutRegistry(): static
    {
        return $this->state(fn (array $attributes) => [
            'registry_id' => null,
        ]);
    }
}
