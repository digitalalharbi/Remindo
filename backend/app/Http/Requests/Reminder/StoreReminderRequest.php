<?php

namespace App\Http\Requests\Reminder;

use App\Support\Tenancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by policy on the controller action
    }

    public function rules(): array
    {
        $orgId = Tenancy::currentId();

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['required', 'date'],
            'issue_date' => ['nullable', 'date', 'before_or_equal:expiry_date'],

            'category_id' => ['nullable', 'uuid', Rule::exists('categories', 'id')],
            'document_id' => ['nullable', 'uuid', Rule::exists('documents', 'id')->where('organization_id', $orgId)],
            'assigned_to' => ['nullable', 'uuid', Rule::exists('users', 'id')],

            'tags' => ['sometimes', 'array'],
            'tags.*' => ['uuid', Rule::exists('tags', 'id')->where('organization_id', $orgId)],

            'recurrence' => ['sometimes', Rule::in(['none', 'monthly', 'yearly', 'custom'])],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:365', 'required_if:recurrence,custom'],
            'recurrence_unit' => ['nullable', Rule::in(['day', 'week', 'month', 'year']), 'required_if:recurrence,custom'],

            'reminder_offsets' => ['sometimes', 'array', 'min:1', 'max:10'],
            'reminder_offsets.*' => ['integer', 'min:0', 'max:365'],
            'channels' => ['sometimes', 'array', 'min:1'],
            'channels.*' => [Rule::in(['email', 'in_app', 'web_push', 'sms', 'whatsapp', 'webhook'])],
            'remind_at_time' => ['sometimes', 'date_format:H:i'],
        ];
    }
}
