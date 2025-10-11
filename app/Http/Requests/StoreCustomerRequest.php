<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'document_number' => 'nullable|string|max:18',
            'cep' => 'nullable|string|max:9',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'birth_date' => 'nullable|date',
            'customer_type' => 'nullable|string|in:regular,vip,corporate',
            'newsletter_subscription' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive,blocked',
            'allow_credit' => 'nullable|boolean',
            'meta' => 'nullable|array',
            'meta.*.key' => 'nullable|string',
            'meta.*.value' => 'nullable|string',
        ];
    }
}