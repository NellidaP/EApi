<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // prepare userdata and remove created_at/updated_at if present
        $userdata = $this->userdata;
        unset($userdata['created_at'], $userdata['updated_at']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name', 'id'),
            'userdata' => $userdata,
        ];
    }
}
