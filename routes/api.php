<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsenController;
use App\Http\Controllers\Api\AuthController;

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

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Absen routes
    Route::apiResource('absen', AbsenController::class);

    // Leave routes
    Route::apiResource('leaves', App\Http\Controllers\LeaveController::class);

    //admin
    Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class);
    Route::post('orders/add-payment/{id}', [App\Http\Controllers\Api\OrderController::class,'addPayment']);

    //group admin routes
    Route::group(['prefix' => 'admin'], function () {
        Route::apiResource('absen', App\Http\Controllers\Api\Admin\AbsenController::class);
    });
});
