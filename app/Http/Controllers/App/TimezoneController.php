<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Support\Timezone;
use Illuminate\Http\JsonResponse;

class TimezoneController extends Controller
{
    /**
     * Return the supported timezones as a value/label set.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Timezone::options(),
        ]);
    }
}
