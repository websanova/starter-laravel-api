<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Return public site settings shared across the app and admin clients.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [],
        ]);
    }
}
