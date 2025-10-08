<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('companies', CompanyController::class);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('products/{product}/meta/{key}', [ProductController::class, 'updateMeta']);
    Route::delete('products/{product}/meta/{key}', [ProductController::class, 'deleteMeta']);

    Route::post('orders/{order}/meta/{key}', [OrderController::class, 'updateMeta']);
    Route::delete('orders/{order}/meta/{key}', [OrderController::class, 'deleteMeta']);


    // Store management routes
    Route::apiResource('stores', StoreController::class)->except(['index', 'update', 'destroy']);

    // Subscription management routes
    Route::post('stores/{store}/subscriptions', [StoreController::class, 'createSubscription']);
    Route::post('stores/{store}/subscriptions/change-plan', [StoreController::class, 'changeSubscriptionPlan']);
    Route::delete('stores/{store}/subscriptions', [StoreController::class, 'cancelSubscription']);

    // Payment method routes
    Route::post('stores/{store}/payment-method', [StoreController::class, 'updatePaymentMethod']);

    // Billing portal route
    Route::get('stores/{store}/billing-portal', [StoreController::class, 'getCustomerPortalUrl']);
});


// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);