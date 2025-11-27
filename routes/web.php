<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

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

    // Pages: catalog & detail
    Route::get('/catalog', [BookingController::class, 'catalog'])->name('catalog');
    Route::get('/detail/{service}', [BookingController::class, 'detail'])->name('detail');

    // Store new booking
    Route::post('/store', action: [BookingController::class, 'store'])->name('store');

    // Get available time slots
    Route::post('/slots', [BookingController::class, 'getAvailableSlots'])->name('slots');

    // Get packages by service
    Route::post('/packages', [BookingController::class, 'getPackagesByService'])->name('packages');

    Route::get('/checkout', [BookingController::class, 'checkout']) ->name('checkout');
    Route::post('/checkout', [BookingController::class, 'store']) ->name('checkout.store');

    // Booking success page
    Route::get('/success/{booking}', [BookingController::class, 'success'])->name('success');
    
    Route::post('/payment/create', [PaymentController::class, 'createTransaction'])
    ->name('payment.create');
});