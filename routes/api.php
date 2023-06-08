<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfficeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
 * Non Authenticated Routes
 */
Route::group(['middleware' => ['api']], function () {
    Route::get('/hello', function () {
        echo 'hello!';
    });
});

/*
 * Authenticated Routes
 */

Route::group(['middleware' => ['api', 'authserver']], function () {
    Route::get('/hello/auth', function () {
        echo 'hello authenticated!';
    });

    Route::group(['prefix' => '/offices'], function () {
        // Post office
        Route::post('/', [OfficeController::class, 'store']);

        // Update office
        Route::patch('/{office}', [OfficeController::class, 'update']);

        // Delete office
        Route::delete('/{office}', [OfficeController::class, 'destroy']);
    });
});
