<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeEmail\StoreRequest;
use App\Services\EmailChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MeEmailController extends Controller
{
    public function __construct(
        protected EmailChangeService $emailChangeService
    ) {}

    /**
     * Request an email change for the authenticated user.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($this->emailChangeService->isThrottled($user)) {
            throw ValidationException::withMessages([
                'email' => [__('responses.email_change.throttled')],
            ]);
        }

        $this->emailChangeService->sendConfirmation($user, $request->validated('email'));

        return response()->json(['message' => __('responses.email_change.sent')]);
    }
}
