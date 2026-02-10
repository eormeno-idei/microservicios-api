<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogViewerController;
use App\Http\Controllers\DocumentationController;

// Log viewer routes (MUST be before dynamic demo route)
// Route::prefix('logs')->group(function () {
//     Route::get('/', [LogViewerController::class, 'index'])->name('logs.index');
//     Route::get('/content', [LogViewerController::class, 'content'])->name('logs.content');
//     Route::get('/download', [LogViewerController::class, 'download'])->name('logs.download');
//     Route::post('/clear', [LogViewerController::class, 'clear'])->name('logs.clear');
// });

// Demo route - Default landing demo
// Route::get('/', function () {
//     $reset = request()->query('reset', false);
//     return view('demo', [
//         'demo' => 'landing',
//         'reset' => $reset
//     ]);
// });

// Route::get('/login', function () {
//     $reset = request()->query('reset', false);
//     return view('demo', [
//         'demo' => 'auth/login',
//         'reset' => $reset
//     ]);
// })->name('login');

// Route::get('/email/verify/{id}/{hash}', function () {
//     $reset = request()->query('reset', false);
//     return view('demo', [
//         'demo' => 'auth/email-verified',
//         'reset' => $reset
//     ]);
// })->middleware('signed')->name('verification.notice');

// Reset Password Route (Landing from Email)
// Route::get('/reset-password', function () {
//     $reset = request()->query('reset', false);
//     return view('demo', [
//         'demo' => 'reset-password',
//         'reset' => $reset
//     ]);
// })->name('password.reset');

// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

//     /*
//      * Auto-Discovery handles this route dynamically now via the catch-all below.
//      * Keeping this block commented as reference or deletion candidate.
//      */
//     /*
//     Route::get('/admin/dashboard', function () {
//         $reset = request()->query('reset', false);
//         return view('demo', [
//             'demo' => 'admin/dashboard',
//             'reset' => $reset
//         ]);
//     })->name('admin.dashboard');
//     */

// });

// // Profile route - Usuario autenticado
// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/profile', function () {
//         $reset = request()->query('reset', false);
//         return view('demo', [
//             'demo' => 'profile',
//             'reset' => $reset
//         ]);
//     })->name('profile');
// });


// Dynamic UI Screen Catcher (Catch-All)
// Allows URLs like /admin/dashboard to resolve to Admin\Dashboard screen class
// Now also handles the root path '/' defaulting to 'landing'
// Must be the LAST route definition to not intercept other specific routes
Route::get('/{screen?}', function (?string $screen = 'home') {
    if ($screen === 'favicon.ico') return abort(404);

    $reset = request()->query('reset', false);
    return view('usim::demo', [
        'demo' => $screen,
        'reset' => $reset
    ]);
})->where('screen', '^(?!api|storage|css|js|images|telescope|_debugbar).*$')->name('ui.catchall');

// Rutas para documentación
// Route::prefix('docs')->group(function () {
//     // Índice principal de documentación
//     Route::get('/', [DocumentationController::class, 'docsIndex'])->name('docs.index');

//     // Documentación principal (3 archivos)
//     Route::get('/api-complete', [DocumentationController::class, 'apiCompleteDocs'])->name('docs.api-complete');
//     Route::get('/implementation-summary', [DocumentationController::class, 'implementationSummaryDocs'])->name('docs.implementation-summary');
//     Route::get('/technical-components', [DocumentationController::class, 'technicalComponentsDocs'])->name('docs.technical-components');

//     // Documentación especializada (2 archivos)
//     Route::get('/email-customization', [DocumentationController::class, 'emailCustomizationDocs'])->name('docs.email-customization');
//     Route::get('/file-upload-examples', [DocumentationController::class, 'fileUploadExamplesDocs'])->name('docs.file-upload-examples');

//     // Rutas de compatibilidad con enlaces antiguos (redirects)
//     Route::get('/api-client', fn() => redirect()->route('docs.api-complete'))->name('docs.api-client.redirect');
//     Route::get('/css-structure', fn() => redirect()->route('docs.technical-components'))->name('docs.css-structure.redirect');
// });
