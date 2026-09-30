<?php

namespace App\Http\Requests\App\PaymentMethodAddress;

use App\Rules\AddressRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Obvious junk and nothing more. The provider verifies the address itself on
     * the write and rejects what it cannot place, so a second set of rules here
     * only risks turning away an address it would have accepted. Country is the
     * exception, since without one there is no tax location to resolve at all.
     * Postal code is not required for the same reason, plenty of countries do
     * not use one.
     */
    public function rules(): array
    {
        return [
            'name' => AddressRules::name(),
            'line1' => AddressRules::line1(required: false),
            'line2' => AddressRules::line2(),
            'city' => AddressRules::city(required: false),
            'state' => AddressRules::state(),
            'postal_code' => AddressRules::postalCode(required: false),
            'country' => AddressRules::country(),
        ];
    }
}
