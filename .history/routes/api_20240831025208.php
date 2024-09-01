<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin');
    Route::post('/auth/login', 'login');
    Route::post('/auth/register', 'register');

    Route::get('/register-hero', 'showRegisterHero');
    Route::post('/auth/hero/register', 'registerHero');

    Route::post('/logout', 'logout');

    Route::get('/forgot-password', 'showForgotPassword');
    Route::post('/forgot-password', 'forgotPassword');
});
