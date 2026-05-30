<?php

use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware('throttle:login')->post('login', [AuthController::class, 'login']);

    Route::post('password/forgot', [PasswordController::class, 'forgot']);
    Route::post('password/reset', [PasswordController::class, 'reset']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::post('2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);
        Route::post('2fa/disable', [TwoFactorController::class, 'disable']);
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::post('users/{user}/role', [UserController::class, 'assignRole']);
});
