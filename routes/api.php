<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\WorkplaceController;

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
        Route::post('/', [OfficeController::class, 'store']);
        Route::patch('/{office}', [OfficeController::class, 'update']);
        Route::delete('/{office}', [OfficeController::class, 'destroy']);
    });

    Route::group(['prefix' => '/workplaces'], function () {
        Route::get('/', [WorkplaceController::class, 'index']);
        Route::post('/', [WorkplaceController::class, 'store']);
        Route::patch('/{workplace}', [WorkplaceController::class, 'update']);
    });

    Route::group(['prefix' => '/regions'], function () {
        Route::get('/', [RegionController::class, 'index']);
    });

    Route::group(['prefix' => '/clients'], function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
    });
});
