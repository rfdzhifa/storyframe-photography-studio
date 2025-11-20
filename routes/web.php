<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Booking Routes
Route::prefix('booking')->name('booking.')->group(function () {
    
    // ========== PUBLIC PAGES ==========
    
    // Main booking page
    Route::get('/', [BookingController::class, 'index'])->name('index');

    // Catalog & detail service
    Route::get('/catalog', [BookingController::class, 'catalog'])->name('catalog');
    Route::get('/detail/{service}', [BookingController::class, 'detail'])->name('detail');

    // Checkout page (form isi data customer)
    Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
    
    // ========== API ENDPOINTS ==========
    
    // Get available time slots
    Route::post('/slots', [BookingController::class, 'getAvailableSlots'])->name('slots');

    // Get packages by service
    Route::post('/packages', [BookingController::class, 'getPackagesByService'])->name('packages');

    // ========== BOOKING PROCESS ==========
    
    // Store new booking (status: Pending, belum bayar)
    Route::post('/store', [BookingController::class, 'store'])->name('store');
    
    // ========== PAYMENT PROCESS ==========
    
    // Halaman payment checkout (pilih metode bayar, tampilkan Snap Midtrans, dll)
    Route::get('/payment/checkout/{booking:booking_code}', [PaymentController::class, 'checkout'])
        ->name('payment.checkout');

    // Create payment transaction (generate Snap Token, Invoice, dll)
    Route::post('/payment/checkout/{booking:booking_code}', [PaymentController::class, 'createTransaction'])
        ->name('payment.transaction');
    
    // ========== PAYMENT CALLBACKS ==========
    
    // Callback dari payment gateway - SUCCESS
    Route::post('/payment/success/{booking:booking_code}', [PaymentController::class, 'paymentSuccess'])
        ->name('payment.success');
    
    // Callback dari payment gateway - FAILED/CANCEL
    Route::get('/payment/failed/{booking:booking_code}', [PaymentController::class, 'paymentFailed'])
        ->name('payment.failed');
    
    // Webhook dari payment gateway (untuk Midtrans/Xendit notification)
    Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
        ->name('payment.webhook');
    
    // ========== SUCCESS PAGE ==========
    
    // Booking success page (setelah payment berhasil)
    Route::get('/success/{booking:booking_code}', [BookingController::class, 'success'])
        ->name('success');
});