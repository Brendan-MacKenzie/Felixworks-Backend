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
    Route::patch('/placements/{placement}/{type}', [ApiController::class, 'managePlacement'])->where(['type' => 'fill|empty']);

    /*
     * Employee endpoints
     */

    /*
     * Hours endpoints
     */

    /*
     * Declaration endpoints
     */
});
