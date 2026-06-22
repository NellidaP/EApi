<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;



Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware([
    //Definir Middleware de autenticación para proteger las rutas, excepto la de login
     'auth:api' 
    ])->group(function () {

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/me', [AuthController::class, 'me']);
        
    Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', PermissionController::class);
    
    Route::get('roles/rolestouser', [RoleController::class, 'assignRolesToUser']);
    Route::apiResource('roles', RoleController::class);

    });