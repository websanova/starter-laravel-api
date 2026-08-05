<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'first_name' => $this->first_name,
            'id' => $this->id,
            'is_on_grace_period' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_grace_period),
            'is_on_trial' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_trial),
            'is_password_reset_required' => $this->is_password_reset_required,
            'is_subscribed' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_subscribed),
            'last_active_at' => $this->last_active_at,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
            'plan' => $this->when($this->relationLoaded('plan'), function () {
                $plan = $this->currentPlan();

                return $plan ? [
                    'features' => $plan->features,
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                ] : null;
            }),
            'role' => $this->roles->first()?->name,
            'timezone' => $this->timezone,
            'trial_ends_at' => $this->trial_ends_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
