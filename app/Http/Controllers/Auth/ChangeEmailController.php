<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangeEmail\StoreRequest;
use App\Services\EmailChangeService;
use Illuminate\Http\JsonResponse;

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
        $this->emailChangeService->confirm(
            $request->validated('email'),
            $request->validated('token')
        );

        return response()->json(['message' => __('responses.email_change.confirmed')]);
    }
}
