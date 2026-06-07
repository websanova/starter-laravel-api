<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\PromotionCodeResource;
use App\Services\PromotionCodeService;
use Illuminate\Http\JsonResponse;

class SubscriptionCouponController extends Controller
{
    /**
     * Look up a promotion code.
     */
    public function show(string $code, PromotionCodeService $promotionCodeService): JsonResponse
    {
        $promotionCode = $promotionCodeService->resolve($code);

        return response()->json([
            'data' => new PromotionCodeResource($promotionCode),
        ]);
    }
}
