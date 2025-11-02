<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Default route (optional)
Route::get('/', function () {
    return view('welcome');
});

// Google OAuth routes — must stay in web.php
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
?>