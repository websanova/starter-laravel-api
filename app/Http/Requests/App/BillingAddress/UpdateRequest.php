<?php

namespace App\Http\Requests\App\BillingAddress;

use App\Rules\AddressRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Country and postal code are the pair a provider resolves a tax location
     * from, so they are the only two held to required. The rest is carried so
     * invoices read properly and can be left out.
     */
    public function rules(): array
    {
        return [
            'name' => AddressRules::name(),
            'line1' => AddressRules::line1(required: false),
            'line2' => AddressRules::line2(),
            'city' => AddressRules::city(required: false),
            'state' => AddressRules::state(),
            'postal_code' => AddressRules::postalCode(),
            'country' => AddressRules::country(),
        ];
    }
}
