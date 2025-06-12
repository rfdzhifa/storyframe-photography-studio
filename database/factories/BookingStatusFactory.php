<?php

namespace Database\Factories;

use App\Models\BookingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingStatus>
 */
class BookingStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = BookingStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Pending', 'Confirmed', 'Cancelled']),
            'color_indicator' => $this->faker->safeColorName(),
        ];
    }
}