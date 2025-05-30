<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudioSchedule;
use Carbon\Carbon;

class StudioScheduleSeeder extends Seeder
{
    public function run()
    {
        $today = Carbon::today();

        for ($i = 0; $i < 7; $i++) {
            StudioSchedule::create([
                'date' => $today->copy()->addDays($i)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
            ]);
        }
    }
}