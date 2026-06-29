<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnityResource extends JsonResource
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
            'direction' => $this->direction,
            'longitud' => $this->longitud,
            'latitud' => $this->latitud,
            'type' => $this->type,
            'tickets' => $this->tickets,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'children' => UnityResource::collection($this->whenLoaded('children')),
            'parent' => new UnityResource($this->whenLoaded('parent')),
        ];
        return parent::toArray($request); 
    }
}
