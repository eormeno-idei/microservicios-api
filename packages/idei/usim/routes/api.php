<?php

use Illuminate\Support\Facades\Route;
use Idei\Usim\Http\Controllers\UIEventController;
use Idei\Usim\Http\Controllers\UploadController;
use Idei\Usim\Http\Controllers\UIController;

// USIM routes require 'web' middleware (session) to maintain UI state
Route::middleware('web')->prefix('api')->group(function () {
    // UI Screen Loader - Exclude API resource roots that collide (GET requests)
    Route::get('/{demo}', [UIController::class, 'show'])
        ->name('api.demo')
        ->where('demo', '^(?!users|user|files|ping).*$');

    // USIM Event Handler
    Route::post('/ui-event', [UIEventController::class, 'handleEvent'])->name('ui.event');

    // USIM Upload Routes
    Route::post('/upload/temporary', [UploadController::class, 'uploadTemporary'])->name('upload.temporary');
    Route::delete('/upload/temporary/{id}', [UploadController::class, 'deleteTemporary'])->name('upload.temporary.delete');
});

Route::middleware('web')->group(function () {
    Route::get('/files/{path}', [UploadController::class, 'serveFile'])->where('path', '.*')->name('files.serve');
});
