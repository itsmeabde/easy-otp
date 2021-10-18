<?php

return [
    // Default lifetime 300 seconds (5 minutes)
    'lifetime' => env('OTP_LIFETIME', 300),
    // Default limiter for request extra time 1 request/120 seconds (2 minutes)
    'limiter_extra_time' => env('OTP_LIMITER_EXTRA_TIME', 120),
    // Default length of digits pin 4
    'digits_pin' => env('OTP_DIGITS_PIN', 4),
    // Default table name
    'table' => 'otp'
];