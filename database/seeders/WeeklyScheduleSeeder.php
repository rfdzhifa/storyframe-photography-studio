<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeeklySchedule;

class WeeklyScheduleSeeder extends Seeder
{
    public function run()
    {
        $days = [
            1 => 'Monday', 
            2 => 'Tuesday', 
            3 => 'Wednesday', 
            4 => 'Thursday', 
            5 => 'Friday', 
            6 => 'Saturday', 
            0 => 'Sunday'
        ];
        
        foreach ($days as $num => $day) {
            WeeklySchedule::create([
                'day_of_week' => $num,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
            ]);
        }
    }
}