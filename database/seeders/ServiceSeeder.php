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
            ['name' => 'Photography', 'description' => 'Professional photo shoot', 'is_active' => true],
            ['name' => 'Videography', 'description' => 'Video recording service', 'is_active' => true],
            ['name' => 'Editing', 'description' => 'Photo and video editing', 'is_active' => true],
        ]);
    }
}