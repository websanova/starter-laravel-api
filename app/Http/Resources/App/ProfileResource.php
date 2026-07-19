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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'plan' => [
                'id' => $this->plan->id,
                'name' => $this->plan->name,
                'slug' => $this->plan->slug,
                'features' => $this->plan->features,
            ],
            'is_complimentary' => $this->is_complimentary,
            'is_subscribed' => $this->is_subscribed,
            'is_on_trial' => $this->is_on_trial,
            'is_on_grace_period' => $this->is_on_grace_period,
            'trial_ends_at' => $this->trial_ends_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

