<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
//use App\Models\Userdata;
use App\Models\Book;

class UserController extends Controller
{

public static function middleware(): array
    {
        return [
            //new Middleware('auth:api', except: [ 'login']),
            // agrega middleware de permisos, requiriendo el permiso "admin"
            // se excluyen las rutas de registro y login
            new Middleware('permission:admin', except: ['index', 'show']),
        ];
    }

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

    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6',
            'age' => 'sometimes',
            'address' => 'sometimes|required|string',
            'dni' => 'sometimes|required|string',
            'birthday' => 'sometimes|required|string',
        ]);

        //dd($request->all());

        $userUpdate = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
        ];

        $userUpdate = array_filter($userUpdate, fn($value) => $value !== null);
        //dd($userUpdate);
        
        $user->update($userUpdate);

        $userdata= [
            'age' => $data['age']??null,
            'address' => $data['address']??null,
            'dni' => $data['dni']??null,
            'birthday' => $data['birthday']??null,
        ];

        //dd($userdata);

       // actualizar o crear userdata relacionado (relación uno a muchos)
        // se toma el primer registro relacionado y se actualiza; si no existe, se crea uno nuevo
        $existing['data'] = ($user->userdata)->getDataJson3('data','data') ?? [];

        //dd($existing);
        
        foreach ($userdata as $key => $value) {
            if ($value !== null && $value !== '') {
                $existing['data'][$key] = $value;
            }
        }

        $user->userdata()->updateOrCreate([], ['data' => json_encode($existing)]);

        $user->refresh(); // Refrescar el modelo del usuario para obtener los datos actualizados

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user
        ], 201) ;
    }

    public function addbook(User $user, Request $request)
    {
        // Validar que el usuario tenga permisos para agregar libros
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un libro
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            
        ]);

        // Agregar el libro al usuario usando la relación morphMany
        $book = $user->books()->create([
            'name' => $request->name,
            'description' => $request->description,
            
        ]);

        return response()->json([
            'message' => 'Libro agregado exitosamente',
            'book' => $book
        ], 200);
    }

    public function addpagetobook(Book $book, Request $request)
    {
        // Validar que el usuario tenga permisos para agregar páginas al libro
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado una página
        $request->validate([
            'content' => 'required|string',
            'page_number' => 'required|integer',
        ]);

        // Agregar la página al libro usando la relación morphMany
        $id = (Str::random(6)) . (Str::subStr(time(), -6));
        //$page = $book->addItem 

        return response()->json([
            'message' => 'Página agregada al libro exitosamente',
            'page' => $page
        ], 200);
    }

    public function addfiletobook( Request $request)
    {
        $request->validate([
            'book_id' => 'required|integer|exists:books,id',
            'file' => 'sometimes|file',
            'files' => 'sometimes|array',
            'files.*' => 'file',
            'type' => 'sometimes|string',
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
        ]);

        $book_id = $request->input('book_id');
        $book = Book::find($book_id);
        if (!$book) {
            return response()->json(['error' => 'Libro no encontrado'], 404);
        }
        // Agregar el archivo al libro usando el trait ModelTrait1
        //'type' => $request->type,
        //'title' => $request->title,
        //'description' => $request->description


        return response()->json([
            'message' => 'Archivo agregado al libro exitosamente',
            'book' => $book
        ], 200);
    }

    public function addfiles(User $user, Request $request)
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
        
        $user->refresh(); // Refresca el modelo para obtener los datos actualizados
        return response()->json([
            'message' => 'Archivo agregado exitosamente',
            'user' => $user
        ], 200);
    }

    public function deletefile(User $user, Request $request)
    {
        // Validar que el usuario tenga permisos para eliminar archivos
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un ID de archivo
        $request->validate([
            'file_id' => 'required|string'
        ]);

        // Eliminar el archivo del usuario usando el trait ModelTrait1
        $datauser = $user->userdata;
        if (!$datauser) {
            return response()->json(['error' => 'No se encontró userdata para este usuario'], 404);
        }
        $datauser->deletefile($request->file_id, 'archivos', 'data', 'documents');

        return response()->json([
            'message' => 'Archivo eliminado exitosamente',
            'user' => $user
        ], 200);
    }

    public function destroy(User $user)
    {
        
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
