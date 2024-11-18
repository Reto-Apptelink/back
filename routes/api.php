<?php

use App\Http\Controllers\Apis\RegisterController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\ProductController;
use App\Http\Controllers\Apis\CustomerController;
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


# Módulo de Autenticación
// Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::middleware('throttle:5,1')->post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [RegisterController::class, 'register'])->name('api.register');
// Route::post('/password/email', [AuthController::class, 'sendPasswordResetLink'])->name('api.sendpasswordResetLink');
// Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('api.resetPassword');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('api.logout');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

## RECORDATORIO: PROTEGER LAS RUTAS AL IMPLEMENTAR EL FRONT
# Módulo de Gestión de Inventarios
// PRODUCTS
Route::get('/products', [ProductController::class, 'getProductCatalog'])->name('api.products.catalog');
Route::post('/register-products', [ProductController::class, 'create'])->name('api.products.create');
Route::put('/product/{id}', [ProductController::class, 'update'])->name('api.products.update');
Route::delete('/remove-product/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');

// CUSTOMER
Route::get('/customers', [CustomerController::class, 'getCustomers'])->name('api.customers.index');
Route::post('/register-customers', [CustomerController::class, 'create'])->name('api.customers.create');
Route::put('/customer/{id}', [CustomerController::class, 'update'])->name('api.customers.update');
Route::delete('/remove-customer/{id}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

# Módulo de Facturación