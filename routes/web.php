<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HolaController;

Route::get('/', fn() => view('api-client'));

Route::get('/bienvenido', fn() => view('welcome'))->name('welcome');

Route::get('/hola-mundo', [HolaController::class, 'saludar'])->name('hola-mundo');

// Parámetro obligatorio
// Route::get('/customers/{id}', function ($id) {
//     return "Cliente ID: {$id}";
// });

// Parámetro opcional
Route::get('/customers/{id?}', function ($id = null) {
    if ($id) {
        return "Cliente ID: {$id}";
    }
    return "Lista de todos los clientes";
});

// Múltiples parámetros
Route::get('/customers/{id}/reviews/{reviewId}', function ($id, $reviewId) {
    return "Review {$reviewId} del cliente {$id}";
});

// Solo números
Route::get('/products/{id}', function ($id) {
    return "Producto: {$id}";
})->where('id', '[0-9]+');

// Solo letras
Route::get('/categories/{slug}', function ($slug) {
    return "Categoría: {$slug}";
})->where('slug', '[a-zA-Z\-]+');

// Expresiones regulares múltiples
Route::get('/reseñas/{mes}/{año}', function ($mes, $año) {
    $ruta = route('reviews-by-month', ['mes' => $mes, 'año' => $año]);
    return "Reseñas de {$mes}/{$año} - Ver en: {$ruta}";
})->where(['año' => '202[0-5]', 'mes' => '[0-9]{2}'])->name('reviews-by-month');

// Ruta para previsualizar emails (solo para desarrollo)
if (app()->environment('local')) {
    Route::get('/email-preview/reset-password', function () {
        $user = App\Models\User::first() ?? App\Models\User::factory()->make([
            'name' => 'Usuario Demo',
            'email' => 'demo@ejemplo.com'
        ]);

        $notification = new App\Notifications\ResetPasswordNotification('demo-token-123456');

        return $notification->toMail($user);
    })->name('email.preview.reset');

    Route::get('/email-preview/verify-email', function () {
        $user = App\Models\User::first() ?? App\Models\User::factory()->make([
            'name' => 'Usuario Demo',
            'email' => 'demo@ejemplo.com'
        ]);

        $notification = new App\Notifications\CustomVerifyEmailNotification();

        return $notification->toMail($user);
    })->name('email.preview.verify');
}

// Ruta para el cliente API
// Route::get('/api-client', fn() => view('api-client'))->name('api-client');

// Rutas para documentación
Route::prefix('docs')->group(function () {
    // Índice principal de documentación
    Route::get('/', [DocumentationController::class, 'docsIndex'])->name('docs.index');

    // Documentación principal (3 archivos)
    Route::get('/api-complete', [DocumentationController::class, 'apiCompleteDocs'])->name('docs.api-complete');
    Route::get('/implementation-summary', [DocumentationController::class, 'implementationSummaryDocs'])->name('docs.implementation-summary');
    Route::get('/technical-components', [DocumentationController::class, 'technicalComponentsDocs'])->name('docs.technical-components');

    // Documentación especializada (2 archivos)
    Route::get('/email-customization', [DocumentationController::class, 'emailCustomizationDocs'])->name('docs.email-customization');
    Route::get('/file-upload-examples', [DocumentationController::class, 'fileUploadExamplesDocs'])->name('docs.file-upload-examples');

    // Rutas de compatibilidad con enlaces antiguos (redirects)
    Route::get('/api-client', fn() => redirect()->route('docs.api-complete'))->name('docs.api-client.redirect');
    Route::get('/css-structure', fn() => redirect()->route('docs.technical-components'))->name('docs.css-structure.redirect');
});
