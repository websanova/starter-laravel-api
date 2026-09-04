<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\SyncPlanPricesProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanPrice\UpdateRequest;
use App\Http\Resources\Admin\PriceResource;
use App\Models\Plan;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PlanPriceController extends Controller
{
    /**
     * Update a plan price's lookup key and sync it from Stripe.
     */
    public function update(UpdateRequest $request, Plan $plan, Price $price, SyncPlanPricesProvider $syncPlanPrices): JsonResponse
    {
        $price->update($request->validated());

        $result = $syncPlanPrices->handlePrice($price);

        if (!$result->success) {
            throw ValidationException::withMessages([
                'lookup_key' => [__("validation.{$result->error}")],
            ]);
        }

        return response()->json([
            'data' => new PriceResource($price),
            'message' => __('responses.admin.plan.price_synced'),
        ]);
    }
}
