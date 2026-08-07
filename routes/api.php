<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UnityController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\JornadaController;
use App\Http\Controllers\Api\TiendaController;
use App\Http\Controllers\Api\ServstoreController;
use App\Http\Controllers\Api\ConfigurationController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\StoreController;



Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware([
    //Definir Middleware de autenticación para proteger las rutas, excepto la de login
     'auth:api' 
    ])->group(function () {

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/me', [AuthController::class, 'me']);
    Route::post('auth/mypermissions', [AuthController::class, 'myPermissions']);
    Route::post('auth/delete', [AuthController::class, 'deleteUser']);
        
    Route::post('users/{user}/addfiles', [UserController::class, 'addfiles']);
    Route::post('users/{user}/deletefile', [UserController::class, 'deletefile']);
    Route::post('users/{user}/addbook', [UserController::class, 'addbook']);
    Route::post('books/{book}/addfiles2page', [BookController::class, 'addfiles2page']);
    Route::post('books/{book}/deletefile2page', [BookController::class, 'deletefile2page']);
    Route::post('books/{book}/deletefile', [BookController::class, 'deletefile']);
    Route::post('books/{book}/deletepage', [BookController::class, 'deletepage']);
    Route::get('books/{book}/showpage', [BookController::class, 'showpage']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', PermissionController::class);


    //Route::post('jornadas/geo', [App\Http\Controllers\Api\JornadaController::class, 'geoloc']);
    Route::post('jornadas/create', [JornadaController::class, 'create']);
    Route::get('jornadas/unities', [JornadaController::class, 'unities']);
    Route::apiResource('jornadas', JornadaController::class);

    Route::get('roles/rolestouser', [RoleController::class, 'assignRolesToUser']);
    Route::apiResource('roles', RoleController::class);

    Route::post('unities/{unity}/adduser', [UnityController::class, 'adduser']);
    Route::post('unities/{unity}/addbook', [UnityController::class, 'addbook']);
    Route::post('unities/{unity}/removeuser', [UnityController::class, 'removeuser']);
    Route::post('unities/{unity}/removebook', [UnityController::class, 'removebook']);
    
    Route::apiResource('unities', UnityController::class);

    Route::apiResource('tiendas', TiendaController::class);
    Route::apiResource('servstores', ServstoreController::class);
    Route::apiResource('configurations', ConfigurationController::class);
    Route::post('service/{service}/changestate', [ServiceController::class, 'changestate']);
    Route::post('services/{service}/addusertoservice', [ServiceController::class, 'addUserToService']);
    Route::post('services/{service}/removeusertoservice', [ServiceController::class, 'removeUserFromService']);
    //Route::post('services/{service}/additem', [ServiceController::class, 'additem']);
    //Route::post('services/{service}/removeitem', [ServiceController::class, 'removeitem']);
    //Route::post('services/{service}/updateitems', [ServiceController::class, 'updateitems']);
  
    Route::apiResource('services', ServiceController::class);
    Route::post('chats/{chat}/addmessage', [ChatController::class, 'addMessage']);
    Route::post('chats/{chat}/deletemessage', [ChatController::class, 'deleteMessage']);
    Route::apiResource('chats', ChatController::class);
    
    Route::apiResource('stores', StoreController::class);

    
    Route::post('inventories/{inventory}/storeorder', [InventoryController::class, 'storeOrder']);
    Route::post('inventories/{inventory}/updateorder/{orderIndex}', [InventoryController::class, 'updateOrder']);
    Route::post('inventories/{inventory}/deleteorder/{orderIndex}', [InventoryController::class, 'deleteOrder']);
    Route::post('inventories/{inventory}/changeorderstate/{orderIndex}', [InventoryController::class, 'changeOrderState']);
    Route::post('inventories/{inventory}/ordertooperation/{orderIndex}', [InventoryController::class, 'orderToOperation']);
    Route::post('inventories/{inventory}/storeoperation', [InventoryController::class, 'storeOperation']);
    Route::post('inventories/{inventory}/updateoperation/{operationIndex}', [InventoryController::class, 'updateOperation']);
    Route::post('inventories/{inventory}/addoperation/{operationIndex}', [InventoryController::class, 'addOperation']);
    Route::post('inventories/{inventory}/applyoperation/{operationIndex}', [InventoryController::class, 'applyOperation']);
    Route::post('inventories/{inventory}/deleteoperation/{operationIndex}', [InventoryController::class, 'deleteOperation']);
    

    Route::apiResource('inventories',InventoryController::class);


    });