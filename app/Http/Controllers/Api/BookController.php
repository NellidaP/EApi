<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\BookResource;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $books = BookResource::collection(Book::getOrPaginate());
        return response()->json($books);
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
    public function show(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            
        ]);

        $book->update($data);
        return response()->json([
            'message' => 'Libro actualizado exitosamente',
            'book' => $book
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
        $book->delete();
        return response()->json([
            'message' => 'Libro eliminado exitosamente',
            'book' => $book
        ], 200);
    }

        public function addfiles2page(Book $book, Request $request)
    {
        // Validar que el usuario tenga permisos para agregar archivos
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un archivo
        /* if (!($request->hasFile('file') || $request->hasFile('files'))) {
            return response()->json(['error' => 'No se ha enviado ningún archivo'], 400);
        } */

        // Agregar el archivo al libro usando el trait ModelTrait1

        $hoja_id = (Str::random(6)) . (Str::subStr(time(), -6));

        

        $book->addfiles2page($request, $hoja_id , null, 'data');

        
        
        $book->refresh(); // Refresca el modelo para obtener los datos actualizados
        return response()->json([
            'message' => 'Archivo agregado exitosamente',
            'book' => $book
        ], 200);
    }

    public function deletefile(Book $book, Request $request)
    {
        // Validar que el usuario tenga permisos para eliminar archivos
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un ID de archivo
        $request->validate([
            'file_id' => 'required|string'
        ]);

        // Eliminar el archivo del libro usando el trait ModelTrait1
        $book->deletefile($request->file_id, 'archivos', 'data', 'documents');

        return response()->json([
            'message' => 'Archivo eliminado exitosamente',
            'book' => $book
        ], 200);
    }

    public function deletepageñoyoglogll(Book $book, Request $request)
    {
        // Validar que el usuario tenga permisos para eliminar páginas
        if (!auth()->user()->can('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validar que se haya enviado un ID de página
        $request->validate([
            'page_id' => 'required|string'
        ]);

        // Eliminar la página del libro usando el trait ModelTrait1
        $book->deleteitem($request->page_id, null, 'data');

        return response()->json([
            'message' => 'Página eliminada exitosamente',
            'book' => $book
        ], 200);
    }
}
