<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController; // Pastikan ini di-import

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Booking Routes
Route::prefix('booking')->name('booking.')->group(function () {
    // Main booking page
    Route::get('/', [BookingController::class, 'index'])->name('index');
    
    // Store new booking
    Route::post('/store', [BookingController::class, 'store'])->name('store');
    
    // Get available time slots
    Route::post('/slots', [BookingController::class, 'getAvailableSlots'])->name('booking.slots');
    
    // Get packages by service
    Route::post('/packages', [BookingController::class, 'getPackagesByService'])->name('packages');
    
    // Booking success page
    Route::get('/success/{booking}', [BookingController::class, 'success'])->name('success');
});