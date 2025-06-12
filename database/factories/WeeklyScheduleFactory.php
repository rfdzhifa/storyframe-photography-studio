<?php

namespace Database\Factories;

use App\Models\WeeklySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyScheduleFactory extends Factory
{
    protected $model = WeeklySchedule::class;

    public function definition(): array
    {
        return [
            'day_of_week' => $this->faker->dayOfWeek, // e.g., Monday
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_available' => true,
        ];
    }
}