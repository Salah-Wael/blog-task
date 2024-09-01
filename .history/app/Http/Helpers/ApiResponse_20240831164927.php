<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function sendResponse($status, $message, $data = [])
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
