<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin');
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout');

    Route::get('/forgot-password', 'showForgotPassword');
    Route::post('/forgot-password', 'forgotPassword');
});
