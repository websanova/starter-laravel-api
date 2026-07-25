<?php

use App\Enums\VerificationMode;

return [

    /*
    |--------------------------------------------------------------------------
    | Verification Mode
    |--------------------------------------------------------------------------
    |
    | Controls how verification behaves per channel. A channel is active
    | when its mode is not "disabled".
    | - "disabled": no verification sent or enforced
    | - "auto": the channel is automatically marked verified
    | - "required": user must verify before accessing protected routes
    |
    | NOTE: Phone is not implemented out of the box as a registration
    | field. Enabling it at signup means adding phone capture to the
    | register flow. Enabling it later instead means building a phone
    | capture and verify flow for existing users. Either way the plumbing
    | here (channel modes, codes, stamping) is already in place.
    |
    */

    'mode' => [
        'email' => VerificationMode::from(env('VERIFICATION_EMAIL_MODE', 'required')),
        'phone' => VerificationMode::from(env('VERIFICATION_PHONE_MODE', 'disabled')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | The number of minutes after registration during which an unverified
    | user can still access protected routes. Only applies when the
    | channel's mode is "required". Set to null or 0 to require
    | immediate verification. Minutes rather than seconds because these
    | windows are typically hours or days, unlike the code timings below
    | which stay in seconds.
    |
    | When both channels are required, staggering the grace periods avoids
    | hitting the user with two prompts at signup. Typically one channel
    | is verified up front with no grace period (usually email) and the
    | other gets a grace period so it kicks in later. Two grace periods
    | spread apart also works. Ultimately it is up to the app and how
    | strictly it wants to confirm each channel.
    |
    */

    'grace_period' => [
        'email' => env('VERIFICATION_EMAIL_GRACE_PERIOD', null),
        'phone' => env('VERIFICATION_PHONE_GRACE_PERIOD', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Length
    |--------------------------------------------------------------------------
    |
    | The number of digits in the verification code.
    |
    */

    'code_length' => env('VERIFICATION_CODE_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Code Expiry
    |--------------------------------------------------------------------------
    |
    | The number of seconds before a verification code expires.
    |
    */

    'code_expiry' => env('VERIFICATION_CODE_EXPIRY', 900),

    /*
    |--------------------------------------------------------------------------
    | Resend Throttle
    |--------------------------------------------------------------------------
    |
    | The number of seconds a user must wait before requesting a new code.
    |
    */

    'resend_throttle' => env('VERIFICATION_RESEND_THROTTLE', 60),

    /*
    |--------------------------------------------------------------------------
    | Max Attempts
    |--------------------------------------------------------------------------
    |
    | The number of failed verification attempts before the code is
    | invalidated and a new one must be requested.
    |
    */

    'max_attempts' => env('VERIFICATION_MAX_ATTEMPTS', 5),

];
