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
            'category' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'critical'])],
            'recurrence' => ['nullable', Rule::in(['none', 'daily', 'weekly', 'monthly', 'yearly'])],
            'schedules' => [Rule::requiredIf($this->isMethod('POST')), 'array', 'min:1', 'max:10'],
            'schedules.*.scheduled_at' => ['required', 'date'],
            'schedules.*.channel' => ['required', Rule::in(['email', 'in_app', 'sms', 'whatsapp'])],
        ];
    }
}
