<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:stores,domain',
            'onboarding' => 'nullable|array',
            'onboarding.payment_method_id' => 'sometimes|string',
        ];
    }
}