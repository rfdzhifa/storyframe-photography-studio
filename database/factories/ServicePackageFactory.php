<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use App\Models\Service;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePackageFactory extends Factory
{
    protected $model = ServicePackage::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'package_id' => Package::factory(),
            'price' => $this->faker->randomFloat(2, 50, 300),
            'is_active' => true,
        ];
    }
}