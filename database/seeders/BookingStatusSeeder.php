<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BookingStatus;

class BookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'color_indicator' => '#f9b115',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Confirmed',
                'color_indicator' => '#2eb85c',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Completed',
                'color_indicator' => '#3399ff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancelled',
                'color_indicator' => '#e55353',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rescheduled',
                'color_indicator' => '#6c757d',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($statuses as $status) {
            BookingStatus::updateOrInsert(
                ['name' => $status['name']],
                $status
            );
        }
    }
}