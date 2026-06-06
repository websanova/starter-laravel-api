<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'plan' => [
                'id' => $this->plan->id,
                'name' => $this->plan->name,
                'slug' => $this->plan->slug,
                'features' => $this->plan->features,
            ],
            'is_subscribed' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_subscribed),
            'is_on_trial' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_trial),
            'is_on_grace_period' => $this->when($this->relationLoaded('subscriptions'), fn () => $this->is_on_grace_period),
            'trial_ends_at' => $this->trial_ends_at,
            'role' => $this->roles->first()?->name,
            'is_password_reset_required' => $this->is_password_reset_required,
            'email_verified_at' => $this->email_verified_at,
            'last_active_at' => $this->last_active_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
