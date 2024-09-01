<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TagController;
use App\Http\Controllers\api\AuthController;

Route::controller(AuthController::class)->group(function () {
    // Route::get('/login', 'showLogin');
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout');
});

Route::prefix('tags')->controller(TagController::class)->group(function () {
    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/', 'index');
    Route::get('/{tag}/edit', 'edit');
    Route::put('/{tagId}', 'update');
    Route::delete('/{tagId}/delete', 'delete');
});
