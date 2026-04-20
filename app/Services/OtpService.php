<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function send(User $user, string $purpose = 'login', ?Request $request = null, bool $enforceCooldown = false): array
    {
        $deliveryChannel = $this->deliveryChannel();

        if ($deliveryChannel === 'email' && empty($user->email)) {
            return $this->response(false, 'email_missing', 'User email address is missing.');
        }

        $phone = $this->normalizePhone((string) $user->phone);
        if ($deliveryChannel === 'sms' && empty($phone)) {
            return $this->response(false, 'phone_missing', 'User phone number is missing.');
        }

        $destination = $deliveryChannel === 'email' ? (string) $user->email : $phone;
        $recipientForStorage = $this->recipientForStorage($destination, $phone);

        $now = now();
        $cooldown = $this->resendCooldownSeconds();

        $result = DB::transaction(function () use ($user, $purpose, $recipientForStorage, $enforceCooldown, $cooldown, $now) {
            $latestActive = OtpCode::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($enforceCooldown && $latestActive) {
                $elapsed = $latestActive->created_at?->diffInSeconds($now) ?? 0;
                if ($elapsed < $cooldown) {
                    return $this->response(
                        false,
                        'cooldown',
                        "Please wait ".($cooldown - $elapsed)." seconds before requesting another OTP.",
                        ['retry_after' => $cooldown - $elapsed]
                    );
                }
            }

            OtpCode::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->update(['used_at' => $now]);

            $latest = OtpCode::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->orderByDesc('id')
                ->first();

            $otp = $this->generateOtp($latest?->otp_hash);

            $otpCode = OtpCode::create([
                'user_id' => $user->id,
                'phone_number' => $recipientForStorage,
                'purpose' => $purpose,
                'otp_hash' => Hash::make($otp),
                'expires_at' => $now->copy()->addMinutes($this->ttlMinutes()),
                'attempts' => 0,
            ]);

            return $this->response(true, 'created', 'OTP generated.', [
                'otp' => $otp,
                'otp_code' => $otpCode,
            ]);
        });

        if (!$result['success']) {
            $this->audit('otp.send.rejected', $user, $result['otp_code'] ?? null, [
                'purpose' => $purpose,
                'reason' => $result['status'],
                'retry_after' => $result['retry_after'] ?? null,
            ], $request);

            return $result;
        }

        /** @var OtpCode $otpCode */
        $otpCode = $result['otp_code'];
        $otp = (string) $result['otp'];

        if ($this->deliveryChannel() === 'internal') {
            $this->audit('otp.sent.internal', $user, $otpCode, [
                'purpose' => $purpose,
                'expires_at' => $otpCode->expires_at?->toIso8601String(),
                'delivery_channel' => 'internal',
            ], $request);

            Log::info('OTP generated via internal Laravel delivery channel.', [
                'user_id' => $user->id,
                'destination' => $destination,
            ]);

            return $this->response(true, 'sent_internal', 'OTP sent successfully.', [
                'otp_code' => $otpCode,
                'provider_error' => null,
                'fallback_otp' => $otp,
                'delivery_info' => 'OTP generated internally by Laravel (no external SMS provider).',
            ]);
        }

        if ($deliveryChannel === 'email') {
            [$sent, $providerError] = $this->sendViaEmail($destination, $otp);
            if ($sent) {
                $this->audit('otp.sent.email', $user, $otpCode, [
                    'purpose' => $purpose,
                    'expires_at' => $otpCode->expires_at?->toIso8601String(),
                    'delivery_channel' => 'email',
                ], $request);

                return $this->response(true, 'sent', 'OTP sent successfully.', [
                    'otp_code' => $otpCode,
                    'provider_error' => null,
                    'delivery_info' => 'OTP sent to your email address.',
                ]);
            }

            if ($this->fallbackEnabled()) {
                $this->audit('otp.sent.fallback', $user, $otpCode, [
                    'purpose' => $purpose,
                    'provider_error' => $providerError,
                    'delivery_channel' => 'email',
                ], $request);

                return $this->response(true, 'sent_fallback', 'OTP sent successfully.', [
                    'otp_code' => $otpCode,
                    'provider_error' => $providerError,
                    'fallback_otp' => $otp,
                ]);
            }

            $otpCode->forceFill(['used_at' => now()])->save();

            $this->audit('otp.send.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'provider_error' => $providerError,
                'delivery_channel' => 'email',
            ], $request);

            return $this->response(false, 'delivery_failed', 'Unable to send OTP at the moment.', [
                'otp_code' => $otpCode,
                'provider_error' => $providerError,
            ]);
        }

        [$sent, $providerError] = $this->sendViaAfricasTalking($destination, $otp);

        if ($sent) {
            $payload = [
                'otp_code' => $otpCode,
                'provider_error' => null,
                'is_sandbox' => $this->isAfricasTalkingSandbox(),
            ];

            if ($this->isAfricasTalkingSandbox()) {
                $payload['fallback_otp'] = $otp;
            }

            $this->audit('otp.sent', $user, $otpCode, [
                'purpose' => $purpose,
                'expires_at' => $otpCode->expires_at?->toIso8601String(),
                'sandbox' => $this->isAfricasTalkingSandbox(),
            ], $request);

            return $this->response(true, 'sent', 'OTP sent successfully.', $payload);
        }

        if ($this->fallbackEnabled()) {
            $this->audit('otp.sent.fallback', $user, $otpCode, [
                'purpose' => $purpose,
                'provider_error' => $providerError,
            ], $request);

            return $this->response(true, 'sent_fallback', 'OTP sent successfully.', [
                'otp_code' => $otpCode,
                'provider_error' => $providerError,
                'fallback_otp' => $otp,
            ]);
        }

        $otpCode->forceFill(['used_at' => now()])->save();

        $this->audit('otp.send.failed', $user, $otpCode, [
            'purpose' => $purpose,
            'provider_error' => $providerError,
        ], $request);

        return $this->response(false, 'delivery_failed', 'Unable to send OTP at the moment.', [
            'otp_code' => $otpCode,
            'provider_error' => $providerError,
        ]);
    }

    public function verify(
        User $user,
        string $otp,
        string $purpose = 'login',
        ?Request $request = null,
        ?string $phone = null
    ): array {
        $submittedPhone = $phone ? $this->normalizePhone($phone) : null;
        $otpCode = OtpCode::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->orderByDesc('id')
            ->first();

        if (!$otpCode) {
            $this->audit('otp.verify.failed', $user, null, [
                'purpose' => $purpose,
                'reason' => 'not_found',
            ], $request);

            return $this->response(false, 'invalid', 'Invalid OTP.');
        }

        if ($submittedPhone && $this->normalizePhone($otpCode->phone_number) !== $submittedPhone) {
            $this->audit('otp.verify.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'reason' => 'phone_mismatch',
            ], $request);

            return $this->response(false, 'invalid', 'Invalid OTP.');
        }

        if ($otpCode->used_at) {
            $this->audit('otp.verify.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'reason' => 'already_used',
            ], $request);

            return $this->response(false, 'used', 'Invalid OTP.');
        }

        if ($otpCode->expires_at && now()->greaterThan($otpCode->expires_at)) {
            $this->audit('otp.verify.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'reason' => 'expired',
            ], $request);

            return $this->response(false, 'expired', 'OTP expired.');
        }

        $maxAttempts = $this->maxAttempts();
        if ($otpCode->attempts >= $maxAttempts) {
            $otpCode->forceFill(['used_at' => now()])->save();

            $this->audit('otp.verify.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'reason' => 'attempt_limit',
            ], $request);

            return $this->response(false, 'locked', 'Invalid OTP.');
        }

        if (!Hash::check($otp, $otpCode->otp_hash)) {
            $otpCode->attempts++;
            if ($otpCode->attempts >= $maxAttempts) {
                $otpCode->used_at = now();
            }
            $otpCode->save();

            $this->audit('otp.verify.failed', $user, $otpCode, [
                'purpose' => $purpose,
                'reason' => 'mismatch',
                'attempts' => $otpCode->attempts,
            ], $request);

            return $this->response(false, $otpCode->attempts >= $maxAttempts ? 'locked' : 'invalid', 'Invalid OTP.', [
                'attempts_left' => max($maxAttempts - $otpCode->attempts, 0),
            ]);
        }

        $otpCode->forceFill(['used_at' => now()])->save();

        $this->audit('otp.verified', $user, $otpCode, [
            'purpose' => $purpose,
            'attempts' => $otpCode->attempts,
        ], $request);

        return $this->response(true, 'verified', 'OTP verified successfully.', [
            'otp_code' => $otpCode,
        ]);
    }

    private function sendViaAfricasTalking(string $to, string $otp): array
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');
        $from = config('services.africastalking.from');
        $verifySsl = (bool) config('services.africastalking.verify_ssl', true);
        $caBundle = config('services.africastalking.ca_bundle');

        if (empty($username) || empty($apiKey)) {
            Log::warning("Africa's Talking credentials missing. OTP dispatch skipped.");

            return [false, "Africa's Talking credentials are missing. Check AFRICASTALKING_USERNAME and AFRICASTALKING_API_KEY."];
        }

        $message = 'Your verification code is '.$otp.'. It will expire in '.$this->ttlMinutes().' minutes.';

        try {
            $verifyOption = true;
            if (!$verifySsl) {
                $verifyOption = false;
            } elseif (!empty($caBundle) && is_string($caBundle) && is_file($caBundle)) {
                $verifyOption = $caBundle;
            }

            $payload = [
                'username' => $username,
                'to' => $to,
                'message' => $message,
            ];

            if (!empty($from)) {
                $payload['from'] = $from;
            }

            $baseUrl = strtolower((string) $username) === 'sandbox'
                ? 'https://api.sandbox.africastalking.com/version1/messaging'
                : 'https://api.africastalking.com/version1/messaging';

            $response = Http::asForm()
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->withOptions([
                    'verify' => $verifyOption,
                    'timeout' => 30,
                ])
                ->post($baseUrl, $payload);

            if (!$response->successful()) {
                $providerError = $response->body();
                if ($response->json('SMSMessageData.Message')) {
                    $providerError = (string) $response->json('SMSMessageData.Message');
                }

                Log::error("Failed to send OTP via Africa's Talking.", [
                    'phone' => $to,
                    'error' => $providerError,
                    'status' => $response->status(),
                ]);

                return [false, "Africa's Talking error: ".$providerError];
            }

            $providerStatus = (string) ($response->json('SMSMessageData.Message') ?? '');
            if (stripos($providerStatus, 'error') !== false) {
                return [false, "Africa's Talking error: ".$providerStatus];
            }

            $recipients = $response->json('SMSMessageData.Recipients', []);
            if (is_array($recipients) && count($recipients) > 0) {
                foreach ($recipients as $recipient) {
                    $status = strtolower((string) ($recipient['status'] ?? ''));
                    if ($status !== 'success') {
                        $recipientError = (string) ($recipient['status'] ?? 'Delivery not confirmed');

                        return [false, "Africa's Talking delivery status: ".$recipientError];
                    }
                }
            }

            Log::info("Africa's Talking accepted OTP message.", [
                'phone' => $to,
                'provider_status' => $providerStatus,
                'recipients' => $recipients,
            ]);

            return [true, null];
        } catch (\Throwable $exception) {
            $errorCode = $exception->getCode();
            $errorMessage = $exception->getMessage();

            Log::error("Failed to send OTP via Africa's Talking.", [
                'phone' => $to,
                'error' => $errorMessage,
                'code' => $errorCode,
            ]);

            $safeMessage = "Africa's Talking error";
            if (is_numeric($errorCode) && (int) $errorCode > 0) {
                $safeMessage .= ' '.$errorCode.': ';
            } else {
                $safeMessage .= ': ';
            }

            return [false, $safeMessage.$errorMessage];
        }
    }

    private function sendViaEmail(string $email, string $otp): array
    {
        $message = 'Your verification code is '.$otp.'. It will expire in '.$this->ttlMinutes().' minutes.';

        try {
            Mail::raw($message, function ($mail) use ($email) {
                $mail->to($email)
                    ->subject(config('app.name', 'AMP').' verification code');
            });

            Log::info('OTP email queued/sent via Laravel Mail.', [
                'email' => $email,
            ]);

            return [true, null];
        } catch (\Throwable $exception) {
            Log::error('Failed to send OTP via email.', [
                'email' => $email,
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);

            return [false, 'Email error: '.$exception->getMessage()];
        }
    }

    private function generateOtp(?string $latestOtpHash = null): string
    {
        $tries = 0;
        do {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $tries++;
        } while ($latestOtpHash && Hash::check($otp, $latestOtpHash) && $tries < 5);

        return $otp;
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

    private function response(bool $success, string $status, string $message, array $extra = []): array
    {
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message,
        ], $extra);
    }

    private function audit(string $action, User $user, ?OtpCode $otpCode, array $changes = [], ?Request $request = null): void
    {
        $payload = [
            'action' => $action,
            'user_id' => $user->id,
            'otp_code_id' => $otpCode?->id,
            'changes' => $changes,
            'ip' => $request?->ip(),
        ];

        Log::info('OTP audit event', $payload);

        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => OtpCode::class,
            'model_id' => $otpCode?->id,
            'changes' => json_encode($changes),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function ttlMinutes(): int
    {
        return max((int) config('otp.ttl', 5), 1);
    }

    private function deliveryChannel(): string
    {
        return strtolower((string) config('otp.delivery_channel', 'internal'));
    }

    private function recipientForStorage(string $destination, string $phone): string
    {
        $recipient = trim($destination);
        if ($recipient === '') {
            $recipient = $phone !== '' ? $phone : 'n/a';
        }

        return substr($recipient, 0, 30);
    }

    private function maxAttempts(): int
    {
        return max((int) config('otp.max_attempts', 5), 1);
    }

    private function resendCooldownSeconds(): int
    {
        return max((int) config('otp.resend_cooldown', 60), 1);
    }

    private function fallbackEnabled(): bool
    {
        return (bool) config('otp.fallback_enabled', false);
    }

    private function isAfricasTalkingSandbox(): bool
    {
        return strtolower((string) config('services.africastalking.username', '')) === 'sandbox';
    }
}
