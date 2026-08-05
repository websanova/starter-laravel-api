<?php

namespace App\Http\Resources\App;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $subscription = $this->subscription();
        $plan = $this->currentPlan();
        $trialEndsAt = $this->trialEndsAt();

        return [
            'avatar_url' => $this->avatar_url,
            'billing_address' => $this->billingAddress() ? new BillingAddressResource($this) : null,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'has_billing_address' => $this->hasBillingAddress(),
            'has_payment_method' => $this->hasDefaultPaymentMethod(),
            'id' => $this->id,
            'is_on_grace_period' => $this->is_on_grace_period,
            'is_on_trial' => $this->is_on_trial,
            'is_subscribed' => $this->is_subscribed,
            'is_verification_pending' => $this->is_verification_pending,
            'is_verification_required' => $this->is_verification_required,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'payment_method' => $this->payment_method,
            'phone' => $this->phone,
            'plan' => $plan ? [
                'features' => $plan->features,
                'id' => $plan->id,
                'name' => $plan->display_name,
                'slug' => $plan->slug,
                'tier' => $plan->tier,
            ] : null,
            'subscription' => $subscription ? [
                'ends_at' => $subscription->ends_at,
                'interval' => Plan::intervalForPriceId($subscription->stripe_price),
                'status' => $subscription->stripe_status,
            ] : null,
            'timezone' => $this->timezone,
            'trial' => $trialEndsAt ? [
                'ends_at' => $trialEndsAt,
            ] : null,
            'updated_at' => $this->updated_at,
            'verification_pending' => $this->verification_pending,
            'verification_required' => $this->verification_required,
        ];
    }
}

