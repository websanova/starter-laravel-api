<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangeEmail\StoreRequest;
use App\Services\EmailChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ChangeEmailController extends Controller
{
    public function __construct(
        protected EmailChangeService $emailChangeService
    ) {}

    /**
     * Confirm the email change using the token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->emailChangeService->confirm(
            $request->validated('email'),
            $request->validated('token')
        );

        if (!$result->success) {
            throw ValidationException::withMessages([
                'token' => [__("responses.{$result->error}")],
            ]);
        }

        return response()->json(['message' => __('responses.email_change.confirmed')]);
    }
}
