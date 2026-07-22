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
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'plan' => [
                'features' => $this->plan->features,
                'id' => $this->plan->id,
                'name' => $this->plan->name,
                'slug' => $this->plan->slug,
            ],
            'timezone' => $this->timezone,
            'trial_ends_at' => $this->trial_ends_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

