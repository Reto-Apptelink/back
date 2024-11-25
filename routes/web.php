<?php

use App\Http\Controllers\Auth\AuthUserController;
use App\Http\Controllers\Gestion\DashboardController;
use App\Http\Controllers\Gestion\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('page.auth.welcome');
})->name('login');    // Ruta formulari de login vista

Route::get('/register', [AuthUserController::class, 'registerIndex'])->name('app.user.register');
Route::get('/password-recovery', [AuthUserController::class, 'passwordRecovery'])->name('app.user.password.recovery');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('app.dashboard');
Route::get('/product', [ProductController::class, 'index'])->name('app.product.index');
Route::get('/product-create', [ProductController::class, 'createForm'])->name('app.product.create.form');
