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
            ['name' => 'Basic', 'description' => '25 minutes session, 10 photos', 'duration_minutes' => 20, 'max_photos' => 10, 'includes_editing' => false, 'is_active' => true],
            ['name' => 'Standard', 'description' => '45 minutes session, 25 photos', 'duration_minutes' => 30, 'max_photos' => 25, 'includes_editing' => true, 'is_active' => true],
            ['name' => 'Premium', 'description' => '60 minutes session, 50 photos', 'duration_minutes' => 60, 'max_photos' => 50, 'includes_editing' => true, 'is_active' => true],
        ]);
    }
}