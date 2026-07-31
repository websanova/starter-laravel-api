<?php

namespace App\Http\Requests\App\SubscriptionResume;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $subscription = $user->subscription();

        return $subscription
            && $subscription->onGracePeriod()
            && !$user->onComplimentary();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
