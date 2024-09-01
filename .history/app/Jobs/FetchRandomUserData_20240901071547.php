<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchRandomUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Make HTTP request to the endpoint
        $response = Http::get('https://randomuser.me/api/');

        // Log the result object
        if ($response->successful()) {
            Log::info('Random User Data:', $response->json());
        } else {
            Log::error('Failed to fetch random user data', ['status' => $response->status()]);
        }
    }
}
