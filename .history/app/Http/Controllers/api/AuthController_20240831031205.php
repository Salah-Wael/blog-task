<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin() {}
    public function login() {}
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'phone_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            // return response(['errors' => $validator->errors()->all()], 422);
            return ApiResponse::sendResponse(422, 'Register Validation Error', $validator->messages()->all());
        }

        $user = User::create([
    }
}
