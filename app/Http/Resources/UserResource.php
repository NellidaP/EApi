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
        $flags =  request('flags');
        $servsuser = null;
        
        $interr = isset($flags['servsuser']['desde']) && isset($flags['servsuser']['hasta']);
        //dd($interr);
        $desde = null;
        $hasta = null;
        if($interr){
            $desde = $flags['servsuser']['desde'];
            $hasta = $flags['servsuser']['hasta'];
            $servsuser=$this->servsuser($desde, $hasta)->get();
            //dd($servsuser);
            
        }


        return [
            'id' => $this->id,
            'name' => $this->name,

            'email' => $this->email,
            'permissions' => $this->getAllPermissions()->pluck( 'id'),
            'unities' => UnityResource::collection($this->whenLoaded('unities')),
            'roles' => auth()->user()->hasAnyPermission(['view-roles','admin']) ? RoleResource::collection($this->whenLoaded('roles')) : null  ,
            'userdata' => new UserdataResource($this->whenLoaded('userdata')),
            'books' => BookResource::collection($this->whenLoaded('books')),
            'activo' => $this->activo,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'servsuser' =>  $servsuser=$this->servsuser($desde, $hasta)->get(),
        ];


        return parent::toArray($request);
    }
}
