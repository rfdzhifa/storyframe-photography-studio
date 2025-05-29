<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return view('pages.home');
});




Route::get('/booking', [BookingController::class, 'create'])->name('booking.form'); // atau booking.create

// Rute ini akan menangani pengiriman data form
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');