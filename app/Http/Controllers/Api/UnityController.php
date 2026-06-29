<?php

namespace App\Http\Controllers\Api;

use App\Models\Unity;
use App\Http\Requests\StoreUnityRequest;
use App\Http\Requests\UpdateUnityRequest;
use App\Http\Resources\UnityResource;
use Illuminate\Http\Request;

class UnityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        //
        return UnityResource::collection(Unity::getOrPaginate());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(request $request)
    {
        //
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'unity_id' => 'nullable|integer',
            'direction' => 'nullable|string',
            'longitud' => 'nullable|string',
            'latitud' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $unity = Unity::create($data);

        return new UnityResource($unity);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unity $unity)
    {
        //
        $unity->load(['users', 'children', 'parent']);
        return new UnityResource($unity);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unity $unity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unity $unity)
    {
        //dd($request->all());
    //
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'unity_id' => 'sometimes|nullable|integer',
            'description' => 'sometimes|nullable|string',
            'direction' => 'sometimes|nullable|string',
            'longitud' => 'sometimes|nullable|string',
            'latitud' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|string',

        ]);

        $unity->update($data);
        $unity->refresh(); // Refresca el modelo para obtener los datos actualizados

        return new UnityResource($unity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unity $unity)
    {
        $unity->delete();

        return response()->noContent();
    }

    public function addfiles(Unity $unity, Request $request)
    {
        // Validar que el usuario tenga permisos para agregar archivos
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un archivo
        if (!($request->hasFile('file') || $request->hasFile('files'))) {
            return response()->json(['error' => 'No se ha enviado ningún archivo'], 400);
        }

        // Agregar el archivo al usuario usando el trait ModelTrait1
        $datauser = $user->userdata;
        if (!$datauser) {
            // Si no existe userdata, crear uno nuevo
            $datauser = $user->userdata()->create();
        }
        $datauser->addfiles($request, 'archivos', 'data', 'documents');
        

        return response()->json([
            'message' => 'Archivo agregado exitosamente',
            'user' => $user
        ], 200);
    }
}
