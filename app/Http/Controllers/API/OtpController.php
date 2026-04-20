<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'phone_number' => 'required|string|max:30',
            'purpose' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($payload['user_id']);
        if ($this->normalizePhone((string) $user->phone) !== $this->normalizePhone($payload['phone_number'])) {
            return response()->json(['message' => 'Phone number does not match the user record.'], 422);
        }

        $result = $this->otpService->send($user, $payload['purpose'] ?? 'login', $request, false);
        if (!$result['success']) {
            $statusCode = $result['status'] === 'cooldown' ? 429 : 422;

            return response()->json([
                'message' => $result['message'],
                'retry_after' => $result['retry_after'] ?? null,
            ], $statusCode);
        }

        return response()->json([
            'message' => 'OTP sent successfully.',
            'expires_in_minutes' => (int) config('otp.ttl', 5),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'phone_number' => 'required|string|max:30',
            'otp' => 'required|digits:6',
            'purpose' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($payload['user_id']);
        $result = $this->otpService->verify(
            $user,
            $payload['otp'],
            $payload['purpose'] ?? 'login',
            $request,
            $payload['phone_number']
        );

        if ($result['success']) {
            return response()->json(['message' => 'OTP verified successfully.']);
        }

        if ($result['status'] === 'expired') {
            return response()->json(['message' => 'OTP expired.'], 422);
        }

        return response()->json(['message' => 'Invalid OTP.'], 422);
    }

    public function resend(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'phone_number' => 'required|string|max:30',
            'purpose' => 'nullable|string|max:50',
        ]);

        $user = User::findOrFail($payload['user_id']);
        if ($this->normalizePhone((string) $user->phone) !== $this->normalizePhone($payload['phone_number'])) {
            return response()->json(['message' => 'Phone number does not match the user record.'], 422);
        }

        $result = $this->otpService->send($user, $payload['purpose'] ?? 'login', $request, true);
        if (!$result['success']) {
            if ($result['status'] === 'cooldown') {
                return response()->json([
                    'message' => $result['message'],
                    'retry_after' => $result['retry_after'] ?? null,
                ], 429);
            }

            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    private function normalizePhone(string $phone): string
    {
        $trimmed = preg_replace('/\s+/', '', $phone) ?? $phone;
        $normalized = preg_replace('/[^\d+]/', '', $trimmed) ?? $trimmed;

        if (str_starts_with($normalized, '00')) {
            return '+'.substr($normalized, 2);
        }

        return $normalized;
    }
}

