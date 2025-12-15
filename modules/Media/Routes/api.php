<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

Route::prefix('api/media')->middleware('api')->group(function () {
    Route::post('/', [MediaController::class, 'store'])->name('media.store');
    Route::get('/models', [MediaController::class, 'getModels'])->name('media.models');
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
});
