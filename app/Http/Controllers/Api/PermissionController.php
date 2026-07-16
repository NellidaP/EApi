<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;
use App\Http\Controllers\Api\Controller;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();
        return PermissionResource::collection($permissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'description' => 'required|string',
        ]);
        $data['guard_name'] = 'api';
        $permission = Permission::create($data);
        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     */
    public function show( $permission)
    {   
        $permission = Permission::find($permission);
        if (!$permission) {
            return response()->json(['error' => 'Permiso no encontrado'], 404);
        }
        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $permission)
    {
        $permission = Permission::find($permission);
        if (!$permission) {
            return response()->json(['error' => 'Permiso no encontrado'], 404);
        }
        $data = $request->validate([
            'name' => 'string|unique:permissions,name,' . $permission->id,
            'description' => 'string',
        ]);
        $data['guard_name'] = 'api';
        $permission->update($data);
        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($permission)
    {
        $permission = Permission::find($permission);
        if (!$permission) {
            return response()->json(['error' => 'Permiso no encontrado'], 404);
        }
        $permission->delete();
        $permission->refresh();
        return new PermissionResource($permission);
    }
}
