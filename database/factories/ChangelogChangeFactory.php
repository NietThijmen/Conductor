<?php

namespace Database\Factories;

use App\Enums\ChangelogChangeType;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangelogChange>
 */
class ChangelogChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'changelog_id' => Changelog::factory(),
            'type' => fake()->randomElement(ChangelogChangeType::cases()),
            'title' => fake()->sentence(),
            'message' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that the change is a breaking change.
     */
    public function asBreaking(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChangelogChangeType::Breaking,
        ]);
    }

    /**
     * Indicate that the change is a new feature.
     */
    public function asNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChangelogChangeType::New,
        ]);
    }

    /**
     * Indicate that the change is an update to existing behavior.
     */
    public function asUpdated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChangelogChangeType::Updated,
        ]);
    }
}
