<?php

namespace App\Http\Controllers\Api;

use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $tiendas = Tienda::all();
        return response()->json($tiendas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $tienda = Tienda::create($request->all());
        return response()->json($tienda, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tienda $tienda)
    {
        return response()->json($tienda);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tienda $tienda)
    {
        //
        $tienda->update($request->all());
        return response()->json($tienda);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tienda $tienda)
    {
        //
        $tienda->delete();
        return response()->json(['message' => 'Tienda eliminada exitosamente'], 200);
    }
}
