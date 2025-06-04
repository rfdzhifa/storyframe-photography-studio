<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Service::insert([
            ['name' => 'Graduation', 'description' => 'Professional Graduation Photo', 'is_active' => true],
            ['name' => 'Wedding Photo', 'description' => 'Wedding photo documentation', 'is_active' => true],
            ['name' => 'Personal Branding', 'description' => 'Professional Profile Photo', 'is_active' => true],
            ['name' => 'Event Coverage', 'description' => 'Coverage of special & formal events', 'is_active' => true],
            ['name' => 'Family Portraits', 'description' => 'Studio quality family photos', 'is_active' => true],

        ]);
    }
}
