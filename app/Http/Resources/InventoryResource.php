<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'product_id' => $this->product_id,
            'stock' => $this->stock,
            'is_infinite' => $this->is_infinite,
            'stock_reserved' => $this->stock_reserved,
            'stock_shipping' => $this->stock_shipping,
            'stock_available' => $this->stock_available,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                    'status' => $this->warehouse->status,
                ];
            }),
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'sku' => $this->product->sku,
                    'name_with_attr' => $this->product->name_with_attr ?? $this->product->name,
                ];
            }),
            'inventory_updates' => $this->whenLoaded('inventory_updates', function () {
                return $this->inventory_updates->map(function ($update) {
                    return [
                        'id' => $update->id,
                        'quantity' => $update->quantity,
                        'type' => $update->type,
                        'created_at' => $update->created_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}