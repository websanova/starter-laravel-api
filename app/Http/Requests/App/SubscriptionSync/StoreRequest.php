<?php

namespace App\Http\Requests\App\SubscriptionSync;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * The client hands over the session it confirmed rather than this hunting
     * for it, since it is the only side that knows which one it was holding.
     */
    public function rules(): array
    {
        return [
            'session' => ['required', 'string', 'max:255'],
        ];
    }
}
