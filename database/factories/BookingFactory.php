<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Package;
use App\Models\BookingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_code' => 'BK-' . strtoupper($this->faker->lexify('????????')),
            'customer_name' => $this->faker->name(),
            'customer_email' => "rfdnazhifah@gmail.com",
            'customer_phone' => $this->faker->phoneNumber(),
            'service_id' => Service::factory(),
            'package_id' => Package::factory(),
            'booking_status_id' => BookingStatus::factory(),
            'booking_date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'total_price' => $this->faker->randomFloat(2, 100, 200),
            'notes' => $this->faker->sentence(),
            'payment_option' => 'cash',
            'down_payment_amount' => 0,
            'payment_status' => 'unpaid',
        ];
    }
}