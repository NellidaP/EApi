<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UnityController;
use App\Http\Controllers\Api\BookController;



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
    
    Route::get('roles/rolestouser', [RoleController::class, 'assignRolesToUser']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('unities', UnityController::class);


    });