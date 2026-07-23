<?php

namespace App\Http\Requests\Reminder;

use Illuminate\Foundation\Http\FormRequest;

class RenewReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_expiry_date' => ['required', 'date', 'after:today'],
        ];
    }
}
