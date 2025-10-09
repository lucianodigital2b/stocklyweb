<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'stock' => 'nullable|integer|min:0',
            'promotional_price' => 'nullable|numeric|min:0',
            'description_seo' => 'nullable|string|max:500',
            'external_code' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:simple,variable,variation',
            'weight' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'lenght' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,archived,draft',
            'product_visibility_id' => 'nullable|integer|exists:product_visibilities,id',
            'published_at' => 'nullable|date',
            'product_id' => 'nullable|integer|exists:products,id',
            'pair_id' => 'nullable|integer|exists:products,id',
            'ean' => 'nullable|string|max:50',
            'ean' => 'nullable|string|max:255',
            'quantityOnShelf' => 'nullable|integer|min:0',
            'allow_backorders' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|json',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];
    }
}
