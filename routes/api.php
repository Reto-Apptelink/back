<?php

use App\Http\Controllers\Apis\RegisterController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\ProductController;
use App\Http\Controllers\Apis\CustomerController;
use App\Http\Controllers\Apis\OrderController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


// Módulo de Autenticación
// Rutas públicas (No requieren autenticación)
Route::post('/register', [RegisterController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Ruta para obtener el usuario autenticado (requiere autenticación con token)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta para cerrar sesión (requiere autenticación con token)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('api.logout');

// Módulo de Gestión de Inventarios (requiere autenticación con token)
Route::middleware('auth:sanctum')->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'getProductCatalog'])->name('api.products.catalog');
    Route::post('/register', [ProductController::class, 'create'])->name('api.products.create');
    Route::get('/{id}', [ProductController::class, 'showProduct'])->name('api.products.show');
    Route::put('/{id}', [ProductController::class, 'update'])->name('api.products.update');
    Route::delete('/remove/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
});

// Módulo de Gestión de Clientes (requiere autenticación con token)
Route::middleware('auth:sanctum')->prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'getCustomers'])->name('api.customers.index');
    Route::post('/register', [CustomerController::class, 'create'])->name('api.customers.create');
    Route::put('/{id}', [CustomerController::class, 'update'])->name('api.customers.update');
    Route::delete('/remove/{id}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');
});

// Módulo de Facturación (requiere autenticación con token)
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'getOrders'])->name('api.orders.index');
    Route::post('/register', [OrderController::class, 'create'])->name('api.orders.create');
});
