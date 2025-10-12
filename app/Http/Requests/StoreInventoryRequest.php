<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
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
            // 'warehouse_id' => 'required|integer|exists:warehouses,id',
            'product_id' => 'required|integer|exists:products,id',
            'stock' => 'required|integer|min:0',
            'is_infinite' => 'boolean',
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
            'warehouse_id.required' => 'O depósito é obrigatório.',
            'warehouse_id.exists' => 'O depósito selecionado não existe.',
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.exists' => 'O produto selecionado não existe.',
            'stock.required' => 'O estoque é obrigatório.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'is_infinite.boolean' => 'O campo estoque infinito deve ser verdadeiro ou falso.',
        ];
    }
}
