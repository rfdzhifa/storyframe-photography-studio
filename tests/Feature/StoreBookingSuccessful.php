<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\WeeklySchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreBookingSuccessful extends TestCase
{
    public function test_store_booking_successful()
    {
        $service = Service::factory()->create();
        $package = Package::factory()->create(['duration_minutes' => 60]);

        ServicePackage::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'price' => 200000,
            'is_active' => true,
        ]);

        $bookingDate = Carbon::now()->addDays(2)->format('Y-m-d');

        WeeklySchedule::factory()->create([
            'day_of_week' => Carbon::parse($bookingDate)->dayOfWeek,
            'is_available' => true,
        ]);

        $status = \App\Models\BookingStatus::factory()->create(['name' => 'Pending']);

        $response = $this->postJson('/booking/store', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '08123456789',
            'service' => $service->id,
            'package' => $package->id,
            'payment' => 'dp',
            'booking_date' => $bookingDate,
            'preferred_time' => '09:00',
            'notes' => 'Test booking',
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment(['success' => true]);
    }

    public function test_store_booking_fails_when_slot_taken()
    {
        $service = Service::factory()->create();
        $package = Package::factory()->create(['duration_minutes' => 60]);

        ServicePackage::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'price' => 200000,
            'is_active' => true,
        ]);

        $bookingDate = Carbon::now()->addDays(2)->format('Y-m-d');

        WeeklySchedule::factory()->create([
            'day_of_week' => Carbon::parse($bookingDate)->dayOfWeek,
            'is_available' => true,
        ]);

        $status = \App\Models\BookingStatus::factory()->create(['name' => 'Pending']);

        Booking::factory()->create([
            'service_id' => $service->id,
            'package_id' => $package->id,
            'booking_status_id' => $status->id,
            'booking_date' => $bookingDate,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $response = $this->postJson('/booking/store', [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone_number' => '08123456788',
            'service' => $service->id,
            'package' => $package->id,
            'payment' => 'dp',
            'booking_date' => $bookingDate,
            'preferred_time' => '09:30',
            'notes' => 'Conflict test',
        ]);

        $response->assertStatus(500)
                ->assertJsonFragment(['success' => false]);
    }
}