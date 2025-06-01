<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_bookings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
        
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('booking_status_id')->constrained('booking_status')->onDelete('restrict');
        
            $table->date('booking_date'); // redundan tapi berguna
            $table->time('start_time');
            $table->time('end_time');
        
            $table->decimal('total_price', 10, 2);
            $table->text('notes')->nullable();
            $table->string('payment_option');
            $table->decimal('down_payment_amount', 10, 2)->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};