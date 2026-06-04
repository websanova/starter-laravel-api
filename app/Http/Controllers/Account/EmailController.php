<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Email\StoreRequest;
use App\Services\EmailChangeService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailController extends Controller
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
            throw new TooManyRequestsHttpException(null, __('responses.email_change.throttled'));
        }

        $this->emailChangeService->sendConfirmation($user, $request->validated('email'));

        return response()->json(['message' => __('responses.email_change.sent')]);
    }
}
