<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::post('auth/me', [AuthController::class, 'me']);




Route::middleware('auth:api')
    ->group(function () {
        
    Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', PermissionController::class);
    
    Route::get('roles/rolestouser', [RoleController::class, 'assignRolesToUser']);
    Route::apiResource('roles', RoleController::class);

    });