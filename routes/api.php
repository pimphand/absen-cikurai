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
Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('banners', App\Http\Controllers\Api\BannerController::class);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Absen routes
    Route::apiResource('absen', AbsenController::class);
    Route::get('absen-check-in', [AbsenController::class, 'hasCheckedInToday']);

    //customers
    Route::apiResource('customers', App\Http\Controllers\Api\CustomerController::class);

    // Leave routes
    Route::apiResource('leaves', App\Http\Controllers\LeaveController::class);

    //admin
    Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class);
    Route::post('orders/add-payment/{id}', [App\Http\Controllers\Api\OrderController::class, 'addPayment']);
    Route::post('orders/shipping-success/{id}', [App\Http\Controllers\Api\OrderController::class, 'addPayment']);

    //group admin routes
    Route::group(['prefix' => 'admin'], function () {
        Route::apiResource('absen', App\Http\Controllers\Api\Admin\AbsenController::class);
        Route::apiResource('master-brand', \App\Http\Controllers\Api\BrandController::class);
    });
});
