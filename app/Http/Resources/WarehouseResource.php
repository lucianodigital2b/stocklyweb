<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'name' => $this->name,
            'status' => $this->status,
            'company_id' => $this->company_id,
            'docks_count' => $this->docks->count(),
            'docks_str' => $this->docas_str,
            'docks' => $this->whenLoaded('docks', function () {
                return $this->docks->map(function ($dock) {
                    return [
                        'id' => $dock->id,
                        'name' => $dock->name,
                        'pivot' => [
                            'processing_days' => $dock->pivot->processing_days,
                            'processing_seconds' => $dock->pivot->processing_seconds,
                            'processing_hours' => $dock->pivot->processing_hours,
                            'extra_fee' => $dock->pivot->extra_fee,
                        ]
                    ];
                });
            }),
            'docks_relation' => $this->whenLoaded('docksRelation', function () {
                return $this->docksRelation->map(function ($warehouseDock) {
                    return [
                        'id' => $warehouseDock->id,
                        'dock_id' => $warehouseDock->dock_id,
                        'processing_days' => $warehouseDock->processing_days,
                        'processing_seconds' => $warehouseDock->processing_seconds,
                        'processing_hours' => $warehouseDock->processing_hours,
                        'extra_fee' => $warehouseDock->extra_fee,
                        'dock' => $warehouseDock->whenLoaded('dock')
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}