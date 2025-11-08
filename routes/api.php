<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;

Route::get('/ping', fn() => response()->json([
    'success' => true,
    'data' => ['status' => 'ok'],
    'message' => 'API is running correctly'
]));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas para reset de contraseña
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Ruta para verificar email
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Reenviar email de verificación
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

    // Rutas para manejo de archivos
    Route::prefix('files')->group(function () {
        Route::post('/upload', [FileController::class, 'upload']);
        Route::get('/', [FileController::class, 'index']);
        Route::get('/download/{filename}', [FileController::class, 'download']);
        Route::delete('/{filename}', [FileController::class, 'delete']);
    });

    // Ejemplo de ruta con permiso específico
    Route::get('/admin/dashboard', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Bienvenido al panel de administración',
            'data' => [
                'stats' => [
                    'users' => 150,
                    'posts' => 320,
                    'comments' => 1240,
                ]
            ]
        ]);
    })->middleware('permission:access-admin-panel');

    // Ejemplo de ruta para usuarios autenticados
    Route::get('/user/profile', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Perfil de usuario',
            'data' => [
                'profile' => [
                    'bio' => 'Usuario activo del sistema',
                    'posts_count' => 15,
                    'followers' => 42,
                ]
            ]
        ]);
    });
});

// Endpoint de prueba para archivos (sin autenticación para testing)
Route::post('/test-files', [FileController::class, 'upload']);
Route::get('/test-files', [FileController::class, 'index']);
Route::get('/test-files/download/{filename}', [FileController::class, 'download']);
Route::delete('/test-files/{filename}', [FileController::class, 'delete']);
