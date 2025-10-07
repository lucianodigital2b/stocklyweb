<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'total_amount' => 'required|numeric',
            'status' => 'required|string|max:50',
            'meta' => 'nullable|array',
            'meta.*.key' => 'required|string',
            'meta.*.value' => 'required|string',
        ];
    }
}