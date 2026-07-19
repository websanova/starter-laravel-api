<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Notification\IndexRequest;
use App\Http\Requests\App\Notification\UpdateRequest;
use App\Http\Resources\App\NotificationResource;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * List the authenticated user's notifications.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->forRead($request->validated('read'))
            ->paginate($request->validated('per_page', 15));

        return response()->json(NotificationResource::paginated($notifications));
    }

    /**
     * Update a single notification.
     */
    public function update(UpdateRequest $request, Notification $notification): JsonResponse
    {
        if ($request->validated('read')) {
            $notification->markAsRead();
        } else {
            $notification->markAsUnread();
        }

        return response()->json(null, 204);
    }
}

