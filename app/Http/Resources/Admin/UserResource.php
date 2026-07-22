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
            'is_complimentary' => $this->when($this->relationLoaded('plan'), fn () => $this->is_complimentary),
            'is_on_grace_period' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_grace_period),
            'is_on_trial' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_trial),
            'is_password_reset_required' => $this->is_password_reset_required,
            'is_subscribed' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_subscribed),
            'last_active_at' => $this->last_active_at,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'plan' => $this->when($this->relationLoaded('plan'), fn () => [
                'features' => $this->plan->features,
                'id' => $this->plan->id,
                'name' => $this->plan->name,
                'slug' => $this->plan->slug,
            ]),
            'role' => $this->roles->first()?->name,
            'timezone' => $this->timezone,
            'trial_ends_at' => $this->trial_ends_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
