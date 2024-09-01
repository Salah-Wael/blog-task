<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin() {}

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Login Validation Error', $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::sendResponse(401, 'Invalid Credentials', []);
        }

        if (!$user->is_verified) {
            return ApiResponse::sendResponse(403, 'Account not verified. Please verify your email.', []);
        }

        $data['token'] = $user->createToken('auth_token')->plainTextToken;
        $data['name'] = $user->name;
        $data['email'] = $user->email;

        return ApiResponse::sendResponse(200, 'User Logged in Successfully', $data);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            // return response(['errors' => $validator->errors()->all()], 422);
            return ApiResponse::sendResponse(422, 'Register Validation Error', $validator->messages()->all());
        }

        $verificationCode = rand(100000, 999999); // Generate a random 6-digit code

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'verification_code' => $verificationCode,
            'is_verified' => false,
        ]);

        Log::info('Verification code for user ' . $user->email . ': ' . $verificationCode);

        $data['token'] = $user->createToken('auth_token')->plainTextToken;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['phone_number'] = $user->phone_number;

        return ApiResponse::sendResponse(201, 'User Registered Successfully', $data);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'verification_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Verification Error', $validator->messages()->all());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponse::sendResponse(404, 'User not found', []);
        }

        if ($user->verification_code === $request->verification_code) {
            $user->is_verified = true;
            $user->verification_code = null; // Optionally clear the verification code
            $user->save();

            return ApiResponse::sendResponse(200, 'User Verified Successfully', []);
        }

        return ApiResponse::sendResponse(400, 'Invalid Verification Code', []);
    }


    public function logout(Request $request){

        $request->user()->currentAccessToken()->delete();

        return ApiResponse::sendResponse(200, 'User Logged out Successfully', []);
    }
}
