<?php

namespace App\Http\Controllers\App;

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
                'verification_required' => [
                    'email' => config('verification.mode.email') === VerificationMode::Required,
                    'phone' => config('verification.mode.phone') === VerificationMode::Required,
                ],
            ],
        ]);
    }
}
