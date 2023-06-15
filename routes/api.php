<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\AgencyController;
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

    Route::group(['prefix' => '/agencies'], function () {
        Route::get('/', [AgencyController::class, 'index']);
        Route::post('/', [AgencyController::class, 'store']);
        Route::patch('/{agency}', [AgencyController::class, 'update']);
        Route::get('/{agency}', [AgencyController::class, 'show']);
    });

    Route::group(['prefix' => '/media'], function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::post('/', [MediaController::class, 'store']);
        Route::delete('/{media}', [MediaController::class, 'destroy']);
        Route::get('/{media}', [MediaController::class, 'show']);
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
        Route::post('/', [ClientController::class, 'store']);
    });
});
