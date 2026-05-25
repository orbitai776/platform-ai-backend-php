<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1/partner')->group(function () {
    
    
    Route::post('/auth/login', [AuthController::class, 'login']);

    
    // Route::middleware(['role.partner'])->group(function () {
    //     Route::get('/organization', [AuthController::class, 'show']);
    //     Route::patch('/organization', [AuthController::class, 'update']);
    // });
});

