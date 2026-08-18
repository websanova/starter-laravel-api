<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\ChangeEmail\StoreRequest;
use App\Services\ConfirmEmailChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ChangeEmailController extends Controller
{
    public function __construct(
        protected ConfirmEmailChangeService $confirmEmailChangeService
    ) {}

    /**
     * Confirm the email change using the token.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->confirmEmailChangeService->handle(
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

