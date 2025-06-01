<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_booking_status_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_status', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color_indicator')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status');
    }
};