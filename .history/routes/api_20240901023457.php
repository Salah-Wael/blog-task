<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\TagController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\PostController;

Route::controller(AuthController::class)->group(function () {
    // Route::get('/login', 'showLogin');
    Route::post('/login', 'login');
    Route::post('/register', 'register');

    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::prefix('tag')->middleware('auth:sanctum')->controller(TagController::class)->group(function () {
    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/index', 'index');
    Route::get('/{tagId}/edit', 'edit');
    Route::put('/{tagId}', 'update');
    Route::delete('/{tagId}/delete', 'delete');
});

Route::prefix('post')->controller(PostController::class)->group(function () {
    Route::get('/create', 'create')->name('news.create')                 ->middleware('checkrole:admin,salesman');
    Route::post('/store', 'store')->name('news.store')                   ->middleware('checkrole:admin,salesman');
    Route::get('/{newsId}', 'show')->name('news.show');
    Route::get('/', 'index')->name('news.index');
    Route::get('/{newsId}/edit', 'edit')->name('news.edit')              ->middleware('checkrole:admin,salesman');
    Route::put('/{newsId}', 'update')->name('news.update')               ->middleware('checkrole:admin,salesman');
    Route::delete('/news/{newsId}/delete', 'delete')->name('news.delete')->middleware('checkrole:admin,salesman');
});