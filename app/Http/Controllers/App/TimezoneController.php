<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TimezoneController extends Controller
{
    /**
     * Return the list of supported IANA timezone identifiers.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Cache::rememberForever('timezones', fn () => DateTimeZone::listIdentifiers()),
        ]);
    }
}
