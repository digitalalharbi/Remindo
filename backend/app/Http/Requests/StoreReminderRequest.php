<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'expires_at' => ['required', 'date'],
            'remind_days_before' => ['required', 'integer', 'min:0', 'max:3650'],
            'channel' => ['required', Rule::in(['email', 'in_app', 'web_push'])],
            'category' => ['nullable', 'string', 'max:64'],
        ];
    }
}
