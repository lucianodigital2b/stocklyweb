<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'metas' => $this->whenLoaded('metas', function () {
                return $this->metas->pluck('value', 'key');
            }),
            'inventory' => $this->whenLoaded('inventory', function () {
                return new InventoryResource($this->inventory);
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return CategoryResource::collection($this->categories);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}