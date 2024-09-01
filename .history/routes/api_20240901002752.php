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
    Route::get('/create', 'create')->name('tag.create');
    Route::post('/store', 'store')->name('tag.store');
    Route::get('/', 'index')->name('tag.index');
    Route::get('/{tag}/edit', 'edit')->name('tag.edit');
    Route::put('/{tagId}', 'update')->name('tag.update');
    Route::delete('/{tagId}/delete', 'delete')->name('tag.delete');
});
