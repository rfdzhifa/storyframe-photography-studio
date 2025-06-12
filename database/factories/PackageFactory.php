<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'duration_minutes' => $this->faker->numberBetween(30, 180),
            'max_photos' => $this->faker->numberBetween(5, 50),
            'includes_editing' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}