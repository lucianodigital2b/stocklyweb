<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'due_at' => 'nullable|date',
            'paid_at' => 'nullable|date',
            'operation' => 'required|integer|in:1,2',
            'payment_method' => 'nullable|in:pix,bank_slip,money,credit,debit',
            'observations' => 'nullable|string|max:1000',
            'external_code' => 'nullable|string|max:255',
            'payment_info' => 'nullable|string|max:500',
            'account' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
        ];
    }
}
