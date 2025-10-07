<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document_number' => $this->document_number,
            'cep' => $this->cep,
            'address' => $this->address,
            'number' => $this->number,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'birth_date' => $this->birth_date,
            'customer_type' => $this->customer_type,
            'newsletter_subscription' => $this->newsletter_subscription,
            'status' => $this->status,
            'store' => $this->whenLoaded('store'),
            'orders_count' => $this->whenLoaded('orders', $this->orders->count()),
            'metas' => $this->whenLoaded('metas', $this->metas->pluck('value', 'key')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}