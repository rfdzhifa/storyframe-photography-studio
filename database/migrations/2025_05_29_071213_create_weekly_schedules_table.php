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
        $table->string('day_of_week'); // ex: Monday, Tuesday, etc.
        $table->time('start_time');
        $table->time('end_time');
        $table->boolean('is_available')->default(true);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};