<?php

namespace App\Http\Controllers\App;

use App\Contracts\ResolvePromotionCodeProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\PromotionCodeVerify\StoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PromotionCodeVerifyController extends Controller
{
    /**
     * Check a promotion code as the user enters it. Nothing is applied here,
     * the code travels with the subscribe call and is resolved again there.
     * This only saves the user finding out it was bad after their card is
     * already stored.
     */
    public function store(StoreRequest $request, ResolvePromotionCodeProvider $promotionCodes): JsonResponse
    {
        $result = $promotionCodes->handle($request->validated('promotion_code'));

        if (!$result->success) {
            throw ValidationException::withMessages([
                'promotion_code' => [__("validation.{$result->error}")],
            ]);
        }

        return response()->json([
            'message' => __('responses.promotion_code.valid'),
        ]);
    }
}
