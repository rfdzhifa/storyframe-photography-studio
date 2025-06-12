<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'is_admin' => true,
        ]);
        $this->actingAs($user);
    }

    public function test_admin_can_access_login()
    {
        auth()->logout(); 
        $response = $this->get('/admin/login');
        $user = User::factory()->create([
            'is_admin' => true,
        ]);
        $this->actingAs($user);
        
        $response->assertStatus(200);
    }
    // public function test_admin_can_access_dashboard()
    // {
    //     $this->get('/admin')->assertStatus(200);
    // }

    // public function test_admin_can_access_booking_index()
    // {
    //     $this->get('/admin/bookings')->assertStatus(200);
    // }

    // public function test_admin_can_access_edit_booking()
    // {
    //     $booking = Booking::factory()->create();
    //     $this->get("/admin/bookings/{$booking->id}/edit")->assertStatus(200);
    // }

}