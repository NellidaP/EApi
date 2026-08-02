<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'chatable_id' => $this->chatable_id,
            'chatable_type' => $this->chatable_type,
            'messages' => json_decode($this->messages),
            'name' => $this->name,  
            'messages' => json_decode($this->messages),
            
        ];
    }
}
