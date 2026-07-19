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

        $flags =  request('flags');
        $servsuser = null;
        $interr = isset($flags['services']['desde']) && isset($flags['services']['hasta']);
        if($interr){
            $desde = $flags['services']['desde'];
            $hasta = $flags['services']['hasta'];
            $servsuser=$this->servicesBetweenDates($desde, $hasta);
        }

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
            'mult' => $this->mult,
            'costo_ficha' => $this->costo_ficha,

            'users' => $this->whenLoaded('users', function () {
                return $this->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => $user->pivot->type,
                    ];
                });
            }),
            'children' => UnityResource::collection($this->whenLoaded('children')),
            'parent' => new UnityResource($this->whenLoaded('parent')),
            'books' => BookResource::collection($this->whenLoaded('books')),
            'services' => $servsuser,
        ];

        return parent::toArray($request);
    }
}
