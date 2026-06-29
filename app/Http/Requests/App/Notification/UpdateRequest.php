<?php

namespace App\Http\Requests\App\Notification;

use App\Rules\NotificationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('notification')->notifiable_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'read' => NotificationRules::read(required: true),
        ];
    }
}

