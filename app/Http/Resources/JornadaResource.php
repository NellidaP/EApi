<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JornadaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'longitud' => $this->longitud,
            'latitud' => $this->latitud,
            'estado' => $this->estado,
            'ent' => $this->ent,
            'aprobador_id' => $this->aprobador_id,
            'comentario' => $this->comentario,
            'url_in' => $this->url_in,
            'url_out' => $this->url_out,
            'unity_in_id' => $this->unity_in_id,
            'unity_out_id' => $this->unity_out_id,
            'fechahora_ini' => $this->fechahora_ini,
            'fechahora_fin' => $this->fechahora_fin,
            'unity_in' => new UnityResource($this->whenLoaded('unityIn')),
            'unity_out' => new UnityResource($this->whenLoaded('unityOut')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
