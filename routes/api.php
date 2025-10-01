<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn() => response()->json([
    'success' => true,
    'data' => ['status' => 'ok'],
    'message' => 'API is running correctly'
]));

// Respuesta JSON en API routes/api.php
Route::get('/status', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::get('/calc/{operation}/{num1}/{num2}', function ($operation, $num1, $num2) {
    $result = 0;
    switch ($operation) {
        case 'add':
            $result = $num1 + $num2;
            break;
        case 'sub':
            $result = $num1 - $num2;
            break;
        case 'mul':
            $result = $num1 * $num2;
            break;
        case 'div':
            $result = $num2 != 0 ? $num1 / $num2 : 'Error: Zero division';
            break;
        default:
            return response()->json(['error' => 'Invalid operator'], 400);
    }
    return response()->json([
        'operation' => $operation,
        'num1' => $num1,
        'num2' => $num2,
        'result' => $result
    ]);
});

Route::post('/stat', function () {
    $validated = request()->validate([
        'numbers' => 'required|array|min:2',
        'numbers.*' => 'numeric',
        'operation' => 'required|in:sum,mean'
    ],
    [
        'numbers.required' => 'Debe proporcionar al menos 2 números.',
        'numbers.array' => 'Los números deben ser un array.',
        'numbers.min' => 'Debe proporcionar al menos 2 números.',
        'numbers.*.numeric' => 'Todos los elementos del array deben ser numéricos.',
        'operation.required' => 'La operación es obligatoria.',
        'operation.in' => 'La operación debe ser: sum o mean.'
    ]);

    $numbers = $validated['numbers'];
    $operation = $validated['operation'];
    $result = null;
    switch ($operation) {
        case 'sum':
            $result = array_sum($numbers);
            break;
        case 'mean':
            $result = array_sum($numbers) / count($numbers);
            break;
    }
    return response()->json(['result' => $result]);
});

// Endpoint de prueba para archivos (sin autenticación para testing)
Route::post('/test-files', [FileController::class, 'upload']);
Route::get('/test-files', [FileController::class, 'index']);
Route::get('/test-files/download/{filename}', [FileController::class, 'download']);
Route::delete('/test-files/{filename}', [FileController::class, 'delete']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas para reset de contraseña
Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Ruta para verificar email (no requiere autenticación)
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
});
