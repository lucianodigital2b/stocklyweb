<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->whenLoaded('customer', $this->customer),
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'metas' => $this->whenLoaded('metas', $this->metas->pluck('value', 'key')),
            'items' => $this->whenLoaded('items', $this->items),
            'payments' => $this->whenLoaded('payments', $this->payments),
            'coupon' => $this->whenLoaded('coupon', $this->coupon),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}