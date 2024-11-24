<?php

use App\Http\Controllers\Auth\AuthUserController;
use App\Http\Controllers\DashboardController;
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
});

Route::get('/register', [AuthUserController::class, 'registerIndex'])->name('app.user.register');
Route::get('/password-recovery', [AuthUserController::class, 'passwordRecovery'])->name('app.user.password.recovery');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth'])->group(function () {
});

