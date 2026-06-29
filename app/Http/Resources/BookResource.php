<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'description' => $this->description,
            'data' => json_decode($this->data),
            'user_id' => $this->user_id,

        ];
        

        return parent::toArray($books);
    }
}
