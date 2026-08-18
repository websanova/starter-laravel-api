<?php

namespace App\Http\Controllers\App;

use App\Enums\VerificationChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Verification\StoreRequest;
use App\Services\ConfirmVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    public function __construct(
        protected ConfirmVerificationService $confirmVerificationService
    ) {}

    /**
     * Verify the user's code.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->confirmVerificationService->handle(
            $request->user(),
            $request->code,
            VerificationChannel::from($request->channel)
        );

        if (!$result->success) {
            throw ValidationException::withMessages([
                'code' => [__("responses.{$result->error}")],
            ]);
        }

        return response()->json(['message' => __('responses.verification.verified')]);
    }
}
