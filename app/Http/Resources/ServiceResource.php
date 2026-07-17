<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'description'=> $this->description,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'tipo_pago' => $this->tipo_pago,
            'n_fichas' => $this->n_fichas,
            'n_personas' => $this->n_personas,
            'costo_ficha' => $this->costo_ficha,
            'tipo_ambiente' => $this->tipo_ambiente,
            'costo_ambiente' => $this->costo_ambiente,
            'costo_asignado' => $this->costo_asignado,
            'costo_hora' => $this->costo_hora,
            'fecha_inicio' => $this->fecha_inicio,
            'tiempo_horas' => $this->tiempo_horas,
            'costo_total' => $this->costo_total,
            'unity_id' => $this->unity_id,
            'user_id' => $this->user_id,
            'users' => $this->users,
            'items' => $this->items,
            'unity' => new UnityResource($this->whenLoaded('unity')),
            'user' => new UserResource($this->whenLoaded('user')),
            
        ];
    }
}
