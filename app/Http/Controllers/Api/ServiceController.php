<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Models\Unity;
use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ServiceResource;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $services = ServiceResource::collection(Service::getOrPaginate());
        return $services;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            //'estado' => 'required|string|max:255',
            'tipo_pago' => 'required|string|max:255',
            'n_fichas' => 'required|integer',
            //'n_personas' => 'required|integer',
            //'costo_ficha' => 'required|numeric',
            //'tipo_ambiente' => 'required|string|max:255',
            //'costo_ambiente' => 'required|numeric',
            'costo_asignado' => 'required|numeric',
            'costo_hora' => 'required|numeric',
            'fecha_inicio' => 'required|date',
            'tiempo_horas' => 'required|numeric|gt:0',
            //'costo_total' => 'required|numeric',
            'unity_id' => 'required|integer|exists:unities,id',
            //'user_id' => 'required|integer|exists:users,id',
            'items' => 'nullable|array',
        ]);
        $data['user_id'] = auth('api')->id(); // Set the user_id to the authenticated user's ID
        $data['estado'] = 0; // Set estado to 0 by default

        $configuration = Configuration::first();

        $matrizColum= [ 'costo_monoambiente',
                        'costo_dos_ambientes',
                        'costo_tres_ambientes',
                        'costo_cuatro_ambientes',];
        
        $data['tipo_ambiente'] = Unity::find($data['unity_id'])->type;
        $data['costo_ambiente'] = $configuration->{$matrizColum[$data['tipo_ambiente'] - 1]};
        $data['users'] = json_encode([], JSON_FORCE_OBJECT);
        if (isset($data['items'])) {
            $data['items'] = json_encode($data['items'], JSON_FORCE_OBJECT);
        } else {
            $data['items'] = json_encode([], JSON_FORCE_OBJECT);
        }
        // $data['items'] = json_encode([], JSON_FORCE_OBJECT);
        $data['fecha_inicio'] = Carbon::parse($data['fecha_inicio'])->format('Y-m-d H:i:s');


        $unity = Unity::find($data['unity_id']);
        if ($unity) {
            $costo_ficha = $unity->costo_ficha;
            if ($costo_ficha ) {
                $data['costo_ficha'] = $costo_ficha;
            } else {
                $data['costo_ficha'] = $unity->parent->costo_ficha;
            }
        } else {
            return response()->json(['error' => 'Unity not found'], 404);
        }

        $data['costo_total'] = $data['costo_asignado'] 
                                + $data['costo_ambiente'] 
                                +$data['costo_hora']*$data['tiempo_horas']
                                + ($data['n_fichas'] * $data['costo_ficha']);

        
    $service = Service::create($data);
    return new ServiceResource($service);



    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }
}
