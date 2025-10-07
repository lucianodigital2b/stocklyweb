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
            'metas' => $this->whenLoaded('metas', $this->metas->pluck('value', 'key')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}