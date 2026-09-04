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
            'name' => $this->name,
            'description' => $this->description,
            'unity_id' => $this->unity_id,
            'unity_name' => $this->unity->name ?? null,
            'type' => $this->type,
            'items' => $this->items,
            'operations' => $this->operations,
            'orders' => $this->orders,
            'has_order_pending' => $this->has_order_pending,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
