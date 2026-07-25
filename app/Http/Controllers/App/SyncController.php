<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Return lightweight counters the client polls for updates.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'notifications_unread' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }
}
