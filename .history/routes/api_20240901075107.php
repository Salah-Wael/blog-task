<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

Route::prefix('post')->middleware('auth:sanctum')->controller(PostController::class)->group(function () {
    Route::get('/create', 'create');
    Route::post('/store', 'store');
    Route::get('/{postId}', 'show');
    Route::get('/index', 'index');
    Route::get('/{postId}/edit', 'edit');
    Route::put('/{postId}/update', 'update');
    Route::delete('/{postId}/delete', 'softDelete');
});

Route::get('/stats', function () {
    // Cache the stats for 10 minutes to reduce database load
    $stats = Cache::remember('stats', 10 * 60, function () {
        return [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'users_with_no_posts' => User::doesntHave('posts')->count(),
        ];
    });

    return response()->json($stats);
});