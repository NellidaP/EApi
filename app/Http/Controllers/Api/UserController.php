<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
//use App\Models\Userdata;

class UserController extends Controller
{
    public function index()
    {
        $users = User::getOrPaginate();

        //return '{"ff": "aca bien"}';
        /* return response()->json([
            'message' => 'Lista de usuarios',
            'data' => UserResource::collection($users)
        ]); */
        return UserResource::collection($users);
    }

    public function store()
    {
        return response()->json([
            'message' => 'Crear usuario',
        ]);
    }

    public function show($user)
    {
        return response()->json([
            'message' => 'Recuperar usuario con id ' . $user,
        ]);
    }

    public function update($user)
    {
        return response()->json([
            'message' => 'Actualizar usuario con id ' . $user,
        ]);
    }

    public function destroy($user)
    {
        return response()->json([
            'message' => 'Eliminar usuario con id ' . $user,
        ]);
    }
}
