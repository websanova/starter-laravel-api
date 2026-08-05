<?php

namespace App\Http\Requests\Admin\UserSubscription;

use Illuminate\Foundation\Http\FormRequest;

class ResumeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $target = $this->route('user');
        $subscription = $target->subscription();

        return $this->user()->can('update', $target)
            && $subscription
            && $subscription->onGracePeriod();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
