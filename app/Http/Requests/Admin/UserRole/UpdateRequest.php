<?php

namespace App\Http\Requests\Admin\UserRole;

use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->input('role');

        if ($role) {
            return $this->user()->can('assignRole', $this->route('user'));
        }

        return $this->user()->can('removeRole', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role' => UserRules::roleNullable(),
        ];
    }
}
