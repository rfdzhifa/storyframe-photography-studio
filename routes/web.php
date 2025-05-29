<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
});
Route::get('/booking', function () {
    return view('pages.booking');
})->name('booking.form');