<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('weekly_schedules', function (Blueprint $table) {
        $table->id();
        $table->integer('day_of_week'); // integer 0-6
        $table->time('start_time');
        $table->time('end_time');
        $table->boolean('is_available')->default(true);
        $table->timestamps();
    });

    // Insert data baru pake angka 0-6
    $days = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    foreach ($days as $num => $day) {
        DB::table('weekly_schedules')->insert([
            'day_of_week' => $num,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};