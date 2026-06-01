<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailReset\StoreRequest;
use App\Services\EmailChangeService;
use Illuminate\Http\JsonResponse;

class EmailResetController extends Controller
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
