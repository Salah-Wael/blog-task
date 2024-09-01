<?php

use Illuminate\Foundation\Inspiring;
use App\Jobs\DeleteOldSoftDeletedPosts;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\FetchRandomUserData;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

$schedule->call(function () {
    DeleteOldSoftDeletedPosts::dispatch();
})->daily();

// Schedule the job to fetch random user data every six hours
$schedule->call(function () {
    FetchRandomUserData::dispatch();
})->everySixHours();