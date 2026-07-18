<?php

namespace App\Http\Controllers\Api;

use App\Models\Servstore;
use App\Http\Requests\UpdateServstoreRequest;
use Illuminate\Http\Request;

class ServstoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $servstores = ServstoreResource::collection(Servstore::getOrPaginate());
        return $servstores;
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
    public function store(Request $request)
    {
        //
        $data = request()->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'costo' => 'required|numeric',
            'tipo' => 'required|string',
            'proveedor' => 'required|string',
        ]);
        $servstore = Servstore::create($data);
        return response()->json($servstore, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Servstore $servstore)
    {
        //
        return response()->json($servstore);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Servstore $servstore)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Servstore $servstore)
    {
        //
        $data = request()->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'costo' => 'sometimes|required|numeric',
            'tipo' => 'sometimes|required|string',
            'proveedor' => 'sometimes|required|string',
        ]);
        $servstore->update($data);
        $servstore->refresh(); // Refresh the model instance to get the latest data
        return response()->json($servstore);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servstore $servstore)
    {
        //
        $servstore->delete();
        return response()->json(['message' => 'Servicio eliminado exitosamente'], 200);
    }
}
