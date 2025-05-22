<?php

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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->foreignId('package_id')->constrained('packages');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('status_id')->constrained('booking_status');
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['DP', 'Full', 'None'])->default('None');
            $table->bigInteger('dp_amount')->nullable();
            $table->bigInteger('total_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_blocks');
    }
};