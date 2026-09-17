<?php

namespace Database\Factories;

use App\Enums\RegistryAuthType;
use App\Models\Registry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registry>
 */
class RegistryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Registry',
            'url' => fake()->unique()->url(),
            'auth_type' => RegistryAuthType::None,
            'auth_config' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the registry requires HTTP basic authentication.
     */
    public function withBasicAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'auth_type' => RegistryAuthType::Basic,
            'auth_config' => [
                'username' => fake()->userName(),
                'password' => fake()->password(),
            ],
        ]);
    }

    /**
     * Indicate that the registry requires a bearer or API token.
     */
    public function withTokenAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'auth_type' => RegistryAuthType::Token,
            'auth_config' => [
                'token' => fake()->sha256(),
            ],
        ]);
    }

    /**
     * Indicate that the registry uses Composer authentication.
     */
    public function withComposerAuth(): static
    {
        return $this->state(fn (array $attributes) => [
            'auth_type' => RegistryAuthType::Composer,
            'auth_config' => [
                'username' => fake()->userName(),
                'password' => fake()->password(),
            ],
        ]);
    }
}
