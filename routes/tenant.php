<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\RequirementsController;
use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Auth;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\CheckResponseForModifications;
use Modules\Core\Http\Controllers\ApplicationController;
use Modules\Core\Http\Controllers\FilePermissionsError;
use Modules\Core\Http\Controllers\FinalizeUpdateController;
use Modules\Core\Http\Controllers\MediaViewController;
use Modules\Core\Http\Controllers\MigrateController;
use Modules\Core\Http\Controllers\OAuthController;
use Modules\Core\Http\Controllers\PrivacyPolicy;
use Modules\Core\Http\Controllers\ScriptController;
use Modules\Core\Http\Controllers\StyleController;
use Modules\Core\Http\Controllers\SynchronizationGoogleWebhookController;
use Modules\Core\Http\Controllers\UpdateDownloadController;
use Modules\Core\Http\Middleware\PreventRequestsWhenMigrationNeeded;
use Modules\Core\Http\Middleware\PreventRequestsWhenUpdateNotFinished;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/id', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
    Route::get('/test', function () {
        return 'HI USer ' . tenant('id');
    });

    Auth::routes(['register' => false, 'verify' => false, 'confirm' => false]);
   


        
    Route::withoutMiddleware(CheckResponseForModifications::class)->group(function () {
        Route::get('/scripts/{script}', [ScriptController::class, 'show']);
        Route::get('/styles/{style}', [StyleController::class, 'show']);
    });

    Route::withoutMiddleware(VerifyCsrfToken::class)->group(function () {
        Route::post('/webhook/google', [SynchronizationGoogleWebhookController::class, 'handle']);
    });

    Route::withoutMiddleware(PreventRequestsWhenUpdateNotFinished::class)->group(function () {
        Route::get('/update/finalize', [FinalizeUpdateController::class, 'show']);
        Route::post('/update/finalize', [FinalizeUpdateController::class, 'finalize']);
    });

    Route::withoutMiddleware([
        PreventRequestsWhenMigrationNeeded::class,
        PreventRequestsWhenUpdateNotFinished::class,
    ])->group(function () {
        // Route::get('/migrate', [MigrateController::class, 'show']);
        // Route::post('/migrate', [MigrateController::class, 'migrate']);

        Route::get('privacy-policy', PrivacyPolicy::class);

        Route::get('/media/{token}', [MediaViewController::class, 'show']);
        Route::get('/media/{token}/download', [MediaViewController::class, 'download']);
        Route::get('/media/{token}/preview', [MediaViewController::class, 'preview']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('/{providerName}/connect', [OAuthController::class, 'connect'])->where('providerName', 'microsoft|google');
        Route::get('/{providerName}/callback', [OAuthController::class, 'callback'])->where('providerName', 'microsoft|google');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/errors/permissions', FilePermissionsError::class);

        Route::get('/patches/{token}/{purchase_key?}', [UpdateDownloadController::class, 'downloadPatch']);
    });

    Route::middleware(['auth'])->group(function () {
        Route::fallback(ApplicationController::class);
});

});




