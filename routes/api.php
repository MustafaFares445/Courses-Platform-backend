<?php

use App\Http\Controllers\API\Admin\AdminUserController;
use App\Http\Controllers\API\Admin\UserController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

        Route::middleware('super-admin')->group(function (): void {
            Route::get('admin-users', [AdminUserController::class, 'index']);
            Route::post('admin-users', [AdminUserController::class, 'store']);
            Route::get('admin-users/{adminUser}', [AdminUserController::class, 'show']);
            Route::patch('admin-users/{adminUser}', [AdminUserController::class, 'update']);
        });
    });
