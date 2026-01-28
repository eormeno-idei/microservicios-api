<?php

use Illuminate\Support\Facades\Route;
use Idei\Usim\Http\Controllers\UIEventController;
use Idei\Usim\Http\Controllers\UploadController;

Route::middleware('api')->prefix('api')->group(function () {
    // USIM Event Handler
    Route::post('/ui-event', [UIEventController::class, 'handleEvent'])->name('ui.event');

    // USIM Upload Routes
    Route::post('/upload/temporary', [UploadController::class, 'uploadTemporary'])->name('upload.temporary');
    Route::delete('/upload/temporary/{id}', [UploadController::class, 'deleteTemporary'])->name('upload.temporary.delete');
});

Route::middleware('web')->group(function () {
    Route::get('/files/{path}', [UploadController::class, 'serveFile'])->where('path', '.*')->name('files.serve');
});
