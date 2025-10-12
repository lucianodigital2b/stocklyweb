<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            'docks' => 'array',
            'docks.*.dock_id' => 'required|integer|exists:docks,id',
            'docks.*.processing_days' => 'integer|min:0',
            'docks.*.processing_seconds' => 'integer|min:0',
            'docks.*.extra_fee' => 'numeric|min:0',
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
            'name.required' => 'O nome do depósito é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'docks.*.dock_id.required' => 'O ID da doca é obrigatório.',
            'docks.*.dock_id.exists' => 'A doca selecionada não existe.',
            'docks.*.processing_days.integer' => 'Os dias de processamento devem ser um número inteiro.',
            'docks.*.processing_seconds.integer' => 'Os segundos de processamento devem ser um número inteiro.',
            'docks.*.extra_fee.numeric' => 'A taxa extra deve ser um valor numérico.',
        ];
    }
}