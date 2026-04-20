<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        return response()->json(['message' => 'API login endpoint is not yet implemented.'], 501);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        return response()->json(['message' => 'API OTP verification endpoint is not yet implemented.'], 501);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        return response()->json(['message' => 'API password reset endpoint is not yet implemented.'], 501);
    }

    public function logout(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        return response()->json(['message' => 'API password update endpoint is not yet implemented.'], 501);
    }
}

