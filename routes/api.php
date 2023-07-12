<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PoolController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\WorkplaceController;
use App\Http\Controllers\CommitmentController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\PlacementTypeController;

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
        Route::get('/{agency}/declarations', [AgencyController::class, 'listDeclarations']);
    });

    Route::group(['prefix' => '/media'], function () {
        Route::post('/', [MediaController::class, 'store']);
        Route::delete('/{media}', [MediaController::class, 'destroy']);
        Route::get('/{media}', [MediaController::class, 'show']);
        Route::get('/{media}/base64', [MediaController::class, 'base64']);
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
        Route::delete('/{workplace}', [WorkplaceController::class, 'destroy']);
    });

    Route::group(['prefix' => '/regions'], function () {
        Route::get('/', [RegionController::class, 'index']);
    });

    Route::group(['prefix' => '/clients'], function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::get('/{client}', [ClientController::class, 'show']);
        Route::patch('/{client}', [ClientController::class, 'update']);
    });

    Route::group(['prefix' => '/locations'], function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::post('/', [LocationController::class, 'store']);
        Route::get('/{location}', [LocationController::class, 'show']);
        Route::patch('/{location}', [LocationController::class, 'update']);
    });

    Route::group(['prefix' => '/addresses'], function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::delete('/{address}', [AddressController::class, 'destroy']);
        Route::patch('/{address}', [AddressController::class, 'update']);
    });

    Route::group(['prefix' => '/placements'], function () {
        Route::post('/', [PlacementController::class, 'store']);
        Route::patch('/{placement}', [PlacementController::class, 'update']);
    });

    Route::group(['prefix' => '/placement-types'], function () {
        Route::post('/', [PlacementTypeController::class, 'store']);
        Route::delete('/{placement_type}', [PlacementTypeController::class, 'destroy']);
        Route::get('/{location}', [PlacementTypeController::class, 'getPlacementTypesByLocation']);
    });

    Route::group(['prefix' => '/pools'], function () {
        Route::get('/', [PoolController::class, 'index']);
        Route::post('/', [PoolController::class, 'store']);
        Route::patch('/{pool}', [PoolController::class, 'update']);
        Route::get('/{pool}', [PoolController::class, 'show']);
        Route::delete('/{pool}', [PoolController::class, 'destroy']);
    });

    Route::group(['prefix' => '/employees'], function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::patch('/{employee}', [EmployeeController::class, 'update']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });

    Route::group(['prefix' => '/postings'], function () {
        Route::get('/', [PostingController::class, 'index']);
        Route::post('/', [PostingController::class, 'store']);
        Route::get('/{posting}', [PostingController::class, 'show']);
        Route::patch('/{posting}', [PostingController::class, 'update']);
        Route::patch('/{posting}/cancel', [PostingController::class, 'cancel']);
    });

    Route::group(['prefix' => '/commitments'], function () {
        Route::post('/', [CommitmentController::class, 'store']);
        Route::patch('/{commitment}', [CommitmentController::class, 'update']);
        Route::get('/{commitment}', [CommitmentController::class, 'show']);
        Route::get('/', [CommitmentController::class, 'index']);
        Route::delete('/{commitment}', [CommitmentController::class, 'destroy']);
    });

    Route::group(['prefix' => '/declarations'], function () {
        Route::post('/', [DeclarationController::class, 'store']);
        Route::patch('/{declaration}', [DeclarationController::class, 'update']);
        Route::delete('/{declaration}', [DeclarationController::class, 'destroy']);
    });
});
