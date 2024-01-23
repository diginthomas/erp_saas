<?php

use App\Http\Controllers\Api\SystemToolsController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        Route::prefix('tools')->group(function () {
            Route::get('clear-cache', [SystemToolsController::class, 'clearCache']);
            Route::get('optimize', [SystemToolsController::class, 'optimize']);
            Route::get('storage-link', [SystemToolsController::class, 'storageLink']);
            Route::get('json-language', [SystemToolsController::class, 'i18n']);
            Route::get('seed-mailables', [SystemToolsController::class, 'seedMailableTemplates']);
        });
    });
});
