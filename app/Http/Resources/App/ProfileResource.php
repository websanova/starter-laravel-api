<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'id' => $this->id,
            'is_complimentary' => $this->is_complimentary,
            'is_on_grace_period' => $this->is_on_grace_period,
            'is_on_trial' => $this->is_on_trial,
            'is_subscribed' => $this->is_subscribed,
            'is_verification_pending' => $this->is_verification_pending,
            'is_verification_required' => $this->is_verification_required,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'phone' => $this->phone,
            'plan' => [
                'features' => $this->plan->features,
                'id' => $this->plan->id,
                'name' => $this->plan->display_name,
                'slug' => $this->plan->slug,
            ],
            'timezone' => $this->timezone,
            'trial_ends_at' => $this->trial_ends_at,
            'updated_at' => $this->updated_at,
            'verification_pending' => $this->verification_pending,
            'verification_required' => $this->verification_required,
        ];
    }
}

