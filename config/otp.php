<?php

return [
    'delivery_channel' => env('OTP_DELIVERY_CHANNEL', 'internal'),
    'ttl' => max((int) env('OTP_TTL', env('AFRICASTALKING_OTP_TTL', 5)), 1),
    'max_attempts' => max((int) env('OTP_MAX_ATTEMPTS', 5), 1),
    'resend_cooldown' => max((int) env('OTP_RESEND_COOLDOWN', 60), 1),
    'fallback_enabled' => (bool) env('OTP_FALLBACK_ENABLED', false),
];
