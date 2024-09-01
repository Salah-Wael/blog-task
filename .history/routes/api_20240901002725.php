<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;

Route::controller(AuthController::class)->group(function () {
    // Route::get('/login', 'showLogin');
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout');
});

Route::prefix('tags')->controller(TagController::class)->group(function () {
    Route::get('/create', 'create')->name('tag.create')           ->middleware('checkrole:admin,salesman');
    Route::post('/store', 'store')->name('tag.store')             ->middleware('checkrole:admin,salesman');
    Route::get('/', 'index')->name('tag.index');
    Route::get('/{tag}/edit', 'edit')->name('tag.edit')           ->middleware('checkrole:admin');
    Route::put('/{tagId}', 'update')->name('tag.update')          ->middleware('checkrole:admin');
    Route::delete('/{tagId}/delete', 'delete')->name('tag.delete')->middleware('checkrole:admin');
});
