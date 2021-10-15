<?php

use App\Http\Controllers\Node\AuthController;
use App\Http\Controllers\Node\PackageController;
use App\Http\Controllers\Node\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Node Routes
|--------------------------------------------------------------------------
|
| Here are the routes specified that are used for node package management.
| This is configured by the App\Providers\RouteServiceProvider, and always
| is assigned to the "node" middlewares.
|
| This is build using the following references:
| - https://github.com/npm/registry/blob/master/docs/REGISTRY-API.md
| - Tests @ https://registry.npmjs.org/
| - Tracking requests send using yarn, npm, pnpm
*/


Route::middleware('registry')->group(function () {
    /**
     * This endpoint is disabled, see url
     * @url https://blog.npmjs.org/post/157615772423/deprecating-the-all-registry-endpoint.html
     */
//    Route::get('/-/all', [SystemController::class, '']);
    Route::get('/-/ping', function () {
        return [];
    });
    Route::get('/', [SystemController::class, 'system']);
    Route::get('/{package}@{version}', [PackageController::class, 'getVersionInfo']);
    Route::get('/{package}', [PackageController::class, 'getInfo']);
    Route::get('/@{scope}/{package}@{version}', [PackageController::class, 'getScopedVersionInfo']);
    Route::get('/@{scope}/{package}', [PackageController::class, 'getScopedInfo']);

    Route::get('/{package}/{version}/{tarname}', [PackageController::class, 'download']);
    Route::get('/@{scope}/{package}/{version}/{tarname}', [PackageController::class, 'downloadScoped']);

    Route::put('/{package}', [PackageController::class, 'put']);
    Route::put('/{package}/-rev/{dummy}', [PackageController::class, 'put']);
    Route::put('/@{scope}/{package}', [PackageController::class, 'putScoped']);
    Route::put('/@{scope}/{package}/-rev/{dummy}', [PackageController::class, 'putScoped']);
    Route::delete('/{package}/-rev/undefined', [PackageController::class, 'delete']);
    Route::delete('/@{scope}/{package}/-rev/undefined', [PackageController::class, 'deleteScoped']);
    /**
     * For now, I'll disregard this function, hence the odd parameters passed to it
     */
//    Route::get('/-/v1/search', [PackageController::class, '']);

    Route::post('/-/v1/login', [AuthController::class, 'login']);
    Route::put('/-/user/org.couchdb.user:{username}', [AuthController::class, 'putUser']);
    Route::delete('/-/user/token/{token}', function () {
        return []; // Could clean the token, but will be auto cleaned also
    });
});

