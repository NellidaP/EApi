<?php

namespace App\Http\Controllers\Api;

use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     */
    public function show(Configuration $configuration)
    {
        //
        return response()->json($configuration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuration $configuration)
    {
        //
        $data = $request->validate([
            'costo_monoambiente' => 'sometimes|required|string',
            'costo_dos_ambientes' => 'sometimes|required|string',
            'costo_tres_ambientes' => 'sometimes|required|string',
            'costo_cuatro_ambientes' => 'sometimes|required|string',
            'email_administrador' => 'sometimes|required|email',
        ]);
        $configuration->update($data);
        return response()->json($configuration);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuration $configuration)
    {
        //
    }
}
