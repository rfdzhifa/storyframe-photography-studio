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
            ['service' => 1, 'package' => 1, 'price' => 100.00, 'is_active' => true],
            ['service' => 1, 'package' => 2, 'price' => 180.00, 'is_active' => true],
            ['service' => 2, 'package' => 3, 'price' => 300.00, 'is_active' => true],
            ['service' => 3, 'package' => 3, 'price' => 300.00, 'is_active' => true],
        ]);
    }
}