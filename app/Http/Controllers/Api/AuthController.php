<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class AuthController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            //new Middleware('auth:api', except: [ 'login']),
            // agrega middleware de permisos, requiriendo el permiso "admin"
            // se excluyen las rutas de registro y login
            new Middleware('permission:admin', except: ['login','me']),
        ];
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $userdata = $user->userdata()->create(); // Crea un registro en user_data para el nuevo usuario

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user
        ], 201) ;
    }

    public function deleteUser(Request $request)
    {
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Eliminar el usuario
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);
        /* $credentials = [
            'email' => "francisco.pintofd@gmail.com",
            'password' => bcrypt('12345678')
        ]; */

        if(User::where('email', $credentials['email'])->count() == 0) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if(User::where('email', $credentials['email'])?->first()?->activo == 0) {
            return response()->json(['error' => 'Usuario inactivo'], 403);
        }

        if (!$token = auth('api')->attempt($credentials) ) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {   $data = auth('api')->user();
        $permissions = auth('api')->user()->getAllPermissions()->pluck('name');
        unset($data['permissions']);
        $data['permissions'] = $permissions;
        return response()->json($data);
    }

    public function myPermissions()
    {
        $user = auth('api')->user();
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json($permissions);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['mensaje' => 'Cierre de sesión exitoso']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
