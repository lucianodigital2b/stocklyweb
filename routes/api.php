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
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockMovementController;
use App\Models\CostCenter;

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
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('cost-centers', CostCenterController::class);
    Route::apiResource('entries', EntryController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('inventories', InventoryController::class);

    // Stock movement routes
    Route::get('inventories/{inventory}/movements', [StockMovementController::class, 'getByInventory']);

    // Additional category routes
    Route::get('categories/tree', [CategoryController::class, 'tree']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('products/{product}/meta/{key}', [ProductController::class, 'updateMeta']);
    Route::delete('products/{product}/meta/{key}', [ProductController::class, 'deleteMeta']);

    Route::post('orders/{order}/meta/{key}', [OrderController::class, 'updateMeta']);
    Route::delete('orders/{order}/meta/{key}', [OrderController::class, 'deleteMeta']);

    // Order history routes
    Route::post('orders/{order}/history', [OrderController::class, 'addHistory']);
    Route::get('orders/{order}/history', [OrderController::class, 'getHistory']);

    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

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

    // Profile management routes
    Route::patch('profile', [ProfileController::class, 'updateProfile']);
    Route::patch('profile/password', [ProfileController::class, 'updatePassword']);
});


// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);