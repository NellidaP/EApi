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
        //$userdata2 = $this->userdata;
        //unset($userdata2['created_at'], $userdata2['updated_at']);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'permisssions' => $this->getAllPermissions()->pluck( 'id'),
            'unities' => UnityResource::collection($this->whenLoaded('unities')),
            'roles' => auth()->user()->hasAnyPermission(['view-roles','admin']) ? RoleResource::collection($this->whenLoaded('roles')) : null  ,
            'userdata' => new UserdataResource($this->whenLoaded('userdata')),
            'books' => BookResource::collection($this->whenLoaded('books')),
        ];


        return parent::toArray($request);
    }
}
