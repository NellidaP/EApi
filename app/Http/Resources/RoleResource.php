<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->permissions = $this->permissions;
        $data = parent::toArray($request);
    $data['permissions'] = $this->permissions->pluck( 'name', 'id'  );
        return $data;
    }
}
