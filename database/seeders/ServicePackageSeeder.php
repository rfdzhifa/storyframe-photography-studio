<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('service_packages')->insert([
            ['service_id' => 1, 'package_id' => 1, 'price' => 50000.00, 'is_active' => true],
            ['service_id' => 1, 'package_id' => 2, 'price' => 75000.00, 'is_active' => true],
            ['service_id' => 2, 'package_id' => 1, 'price' => 100000.00, 'is_active' => true],
            ['service_id' => 2, 'package_id' => 3, 'price' => 120000.00, 'is_active' => true],
            ['service_id' => 3, 'package_id' => 1, 'price' => 150000.00, 'is_active' => true],
            ['service_id' => 3, 'package_id' => 2, 'price' => 180000.00, 'is_active' => true],
            ['service_id' => 3, 'package_id' => 3, 'price' => 300000.00, 'is_active' => true],
            ['service_id' => 4, 'package_id' => 1, 'price' => 300000.00, 'is_active' => true],
            ['service_id' => 4, 'package_id' => 2, 'price' => 180000.00, 'is_active' => true],
            ['service_id' => 4, 'package_id' => 3, 'price' => 323000.00, 'is_active' => true],
            ['service_id' => 5, 'package_id' => 1, 'price' => 150000.00, 'is_active' => true],
            ['service_id' => 5, 'package_id' => 2, 'price' => 180000.00, 'is_active' => true],
        ]);
    }
}
