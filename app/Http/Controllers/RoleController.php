<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;
use App\Models\User;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'required|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $data['guard_name'] = 'api';
        $role = Role::create($data);
        $role->permissions()->attach($permissions);
        //$role->syncPermissions($permissions);

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show( $role)
    {
        $role = Role::find($role);
        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);     
        }

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $role)
    {
        $role = Role::find($role);
        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }
        $data = $request->validate([
            'name' => 'string|unique:roles,name,' . $role->id,
            'description' => 'string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $data['guard_name'] = 'api';
        $role->update($data);
        $role->permissions()->sync($permissions);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $role)
    {
        $role = Role::find($role);
        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }
        $role->delete();
        $role->refresh();
        return new RoleResource($role);
    }

    public function assignRolesToUser(Request $request)
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($data['user_id']);
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $roles = Role::whereIn('id', $data['roles'])->get();
        $user->syncRoles($roles);

        return response()->json(['message' => 'Roles asignados correctamente']);
    }
}
