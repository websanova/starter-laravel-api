<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationChannel;
use App\Enums\VerificationMode;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Return public site settings shared across the app and admin clients.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'verification_code_length' => config('verification.code_length'),
                'verification_required' => collect(VerificationChannel::cases())
                    ->filter(fn (VerificationChannel $channel) => config("verification.mode.{$channel->value}") === VerificationMode::Required)
                    ->map(fn (VerificationChannel $channel) => $channel->value)
                    ->values()
                    ->all(),
            ],
        ]);
    }
}
