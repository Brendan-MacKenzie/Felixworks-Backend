<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the client side of the application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sync\ApiController;

Route::group(['middleware' => 'authclient'], function () {
    /*
     * Posting endpoints
     */
    Route::get('/postings/{posting}/sync', [ApiController::class, 'sync']);

    /*
     * Placement endpoints
     */
    Route::group(['prefix' => '/placements'], function () {
        // Fill|Empty Placement
        Route::patch('/{placement}/{type}', [ApiController::class, 'managePlacement'])->where(['type' => 'fill|empty']);

        // Finalize Placement
        Route::patch('/{placement}/register', [ApiController::class, 'registerHours']);
    });

    /*
     * Employee endpoints
     */
    Route::group(['prefix' => '/employees'], function () {
        // Create or Update employee
        Route::post('/', [ApiController::class, 'createOrUpdateEmployee']);

        // Upload avatar
        Route::post('/{employee}/avatar', [ApiController::class, 'uploadAvatar']);
    });

    /*
     * Declaration endpoints
     */
    Route::group(['prefix' => '/declarations'], function () {
        // Create Declaration
        Route::post('/', [ApiController::class, 'storeDeclaration']);

        // Update Declaration
        Route::patch('/{declaration}', [ApiController::class, 'updateDeclaration']);

        // Delete Declaration
        Route::delete('/{declaration}', [ApiController::class, 'destroyDeclaration']);
    });
});
