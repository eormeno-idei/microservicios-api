<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UIDemoController;
use App\Http\Controllers\UIEventController;
use App\Http\Controllers\LogViewerController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\UploadController;

// Log viewer routes (MUST be before dynamic demo route)
Route::prefix('logs')->group(function () {
    Route::get('/', [LogViewerController::class, 'index'])->name('logs.index');
    Route::get('/content', [LogViewerController::class, 'content'])->name('logs.content');
    Route::get('/download', [LogViewerController::class, 'download'])->name('logs.download');
    Route::post('/clear', [LogViewerController::class, 'clear'])->name('logs.clear');
});

// Demo route - Default landing demo
Route::get('/', function () {
    $reset = request()->query('reset', false);
    return view('demo', [
        'demo' => 'landing-demo',
        'reset' => $reset
    ]);
});

Route::get('/login', function () {
    $reset = request()->query('reset', false);
    return view('demo', [
        'demo' => 'login',
        'reset' => $reset
    ]);
})->name('login');

Route::get('/email/verify/{id}/{hash}', function () {
    $reset = request()->query('reset', false);
    return view('demo', [
        'demo' => 'email-verified',
        'reset' => $reset
    ]);
})->middleware('signed')->name('verification.notice');

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        $reset = request()->query('reset', false);
        return view('demo', [
            'demo' => 'admin-dashboard',
            'reset' => $reset
        ]);
    })->name('admin.dashboard');

});

// Demo route - Dynamic demo viewer
Route::get('/demo/{demo}', function (string $demo) {
    $reset = request()->query('reset', false);
    return view('demo', [
        'demo' => $demo,
        'reset' => $reset
    ]);
})->name('demo');

// Demo UI API routes - Unified controller for all demo services
Route::get('/api/{demo}', [UIDemoController::class, 'show'])->name('api.demo');

// UI Event Handler
Route::post('/api/ui-event', [UIEventController::class, 'handleEvent'])->name('ui.event');

// Upload temporal routes (para demos)
Route::post('/api/upload/temporary', [UploadController::class, 'uploadTemporary'])->name('upload.temporary');
Route::delete('/api/upload/temporary/{id}', [UploadController::class, 'deleteTemporary'])->name('upload.temporary.delete');

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
