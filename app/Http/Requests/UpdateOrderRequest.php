<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'sometimes|integer|exists:customers,id',
            'total_amount' => 'sometimes|numeric',
            'status' => 'sometimes|string|max:50',
            'meta' => 'nullable|array',
            'meta.*.key' => 'required|string',
            'meta.*.value' => 'required|string',
        ];
    }
}