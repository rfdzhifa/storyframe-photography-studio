<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\WeeklySchedule;
use App\Models\BookingStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingSuccessMail;
use Illuminate\Support\Facades\DB;


class GetAvailableSlotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_slots_returns_slots_when_available()
{
    $service = Service::factory()->create();
    $package = Package::factory()->create(['duration_minutes' => 60]);

    ServicePackage::factory()->create([
        'service_id' => $service->id,
        'package_id' => $package->id,
        'is_active' => true,
    ]);

    $today = Carbon::parse('next monday');

    $schedule = WeeklySchedule::factory()->make([
        'day_of_week' => $today->format('l'),
        'is_available' => true,
    ]);

    $schedule = \Mockery::mock($schedule)->makePartial();
    $schedule->shouldReceive('generateSlots')->andReturn([
        ['start' => '09:00', 'end' => '10:00'],
        ['start' => '10:00', 'end' => '11:00'],
    ]);
    $schedule->save();

    $response = $this->postJson('/booking/slots', [
        'date' => $today->toDateString(),
        'package' => $package->id,
    ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThan(0, count($data));
}


    public function test_get_available_slots_returns_error_when_studio_closed()
    {
        $package = Package::factory()->create();

        $date = Carbon::now()->addDays(2)->format('Y-m-d');

        $response = $this->postJson('/booking/slots', [
            'date' => $date,
            'package' => $package->id,
        ]);

        $response->assertStatus(404)
                 ->assertJson([
                     'error' => 'Studio not available on this day.',
                 ]);
    }
    
    public function test_user_can_successfully_book()
{
    $service = Service::factory()->create();
    $package = Package::factory()->create(['duration_minutes' => 60]);
    $bookingStatus = BookingStatus::factory()->create(['name' => 'Pending']);

    // pivot price
    DB::table('service_packages')->insert([
        'service_id' => $service->id,
        'package_id' => $package->id,
        'price' => 500000
    ]);

    // available schedule (biar gak error studio tutup)
    WeeklySchedule::factory()->create([
        'day_of_week' => now()->addDay()->dayOfWeek,
        'is_available' => true,
    ]);

    $data = [
        'full_name' => 'Jipa',
        'email' => 'jipa@gmail.com',
        'phone_number' => '081234567890',
        'service' => $service->id,
        'package' => $package->id,
        'payment' => 'dp',
        'booking_date' => now()->addDay()->format('Y-m-d'),
        'preferred_time' => '14:00',
        'notes' => 'Tolong lighting-nya yang bagus ya',
    ];

    Mail::fake();

    $response = $this->postJson(route('booking.store'), $data);

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'message' => true,
                 'data' => true
             ]);

    $this->assertDatabaseHas('bookings', [
        'customer_name' => 'Jipa',
        'payment_option' => 'dp',
        'payment_status' => 'pending',
    ]);

    Mail::assertSent(BookingSuccessMail::class);
}

}