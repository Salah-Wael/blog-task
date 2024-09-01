<?php

namespace App\Http\Helpers;

class ApiResponse
{
    public static function sendResponse($status = 200, $message = null, $data = [])
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
