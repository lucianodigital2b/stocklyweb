<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
            // 'warehouse_id' => 'sometimes|integer|exists:warehouses,id',
            'product_id' => 'sometimes|integer|exists:products,id',
            'stock' => 'sometimes|integer|min:0',
            'is_infinite' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'warehouse_id.exists' => 'O depósito selecionado não existe.',
            'product_id.exists' => 'O produto selecionado não existe.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'is_infinite.boolean' => 'O campo estoque infinito deve ser verdadeiro ou falso.',
        ];
    }
}
