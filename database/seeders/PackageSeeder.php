<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Package::insert([
            ['name' => 'Basic', 'description' => '1 hour session, 10 photos', 'duration_hours' => 1, 'max_photos' => 10, 'includes_editing' => false, 'is_active' => true],
            ['name' => 'Standard', 'description' => '2 hours session, 25 photos', 'duration_hours' => 2, 'max_photos' => 25, 'includes_editing' => true, 'is_active' => true],
            ['name' => 'Premium', 'description' => '4 hours session, 50 photos', 'duration_hours' => 4, 'max_photos' => 50, 'includes_editing' => true, 'is_active' => true],
        ]);
    }
}