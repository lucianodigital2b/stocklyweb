<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'sometimes|integer|exists:stores,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $this->route('customer'),
            'phone' => 'nullable|string|max:20',
            'meta' => 'nullable|array',
            'meta.*.key' => 'required|string',
            'meta.*.value' => 'required|string',
        ];
    }
}