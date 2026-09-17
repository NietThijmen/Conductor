<?php

namespace Database\Factories;

use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Changelog>
 */
class ChangelogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'old_version' => fake()->semver(),
            'new_version' => fake()->semver(),
            'status' => ChangelogStatus::Backlog,
            'title' => fake()->sentence(),
            'summary' => fake()->paragraph(),
        ];
    }

    /**
     * Set the changelog status to checking.
     */
    public function checking(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChangelogStatus::Checking,
        ]);
    }

    /**
     * Set the changelog status to checked.
     */
    public function checked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ChangelogStatus::Checked,
        ]);
    }
}
