<?php

use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
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
Route::get('/', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

Route::middleware(['auth'])->group(function () {

Route::prefix('dashboard')->name('dashboard.')->group(function(){
    Route::get('/',[DashboardAdminController::class,'index'])->name('admin.dashboard');
    Route::post('/table', [DashboardAdminController::class, 'table'])->name('table');
});

Route::prefix('user')->name('user.')->group(function(){
    Route::get('/',[UserController::class,'user'])->name('user.index');
    Route::post('/table', [UserController::class, 'table'])->name('table');
    Route::get('/create',[UserController::class,'create'])->name('create');
    Route::get('/update/{id}',[UserController::class,'update'])->name('update');
    Route::post('/updateform',[UserController::class,'updateform'])->name('updateform');
    Route::post('/createform',[UserController::class,'createform'])->name('createform');
    Route::post('/deleteform',[UserController::class,'deleteform'])->name('deleteform');
});

Route::prefix('menu')->name('menu.')->group(function(){
    Route::get('/',[MenuController::class,'menu'])->name('menu');
    Route::post('/table', [MenuController::class, 'table'])->name('table');
    Route::get('/create',[MenuController::class,'create'])->name('create');
    Route::get('/update/{id}',[MenuController::class,'update'])->name('update');
    Route::post('/updateform',[MenuController::class,'updateform'])->name('updateform');
    Route::post('/createform',[MenuController::class,'createform'])->name('createform');
    Route::post('/deleteform',[MenuController::class,'deleteform'])->name('deleteform');
});

Route::prefix('order')->name('order.')->group(function(){
    Route::get('/',[OrderController::class,'order'])->name('order');
    Route::post('/table', [OrderController::class, 'table'])->name('table');
    Route::get('/lihat/{id}',[OrderController::class,'lihat'])->name('lihat');
});

});

Route::get('/payment/{id}', [OrderApiController::class, 'show_midtrans']);

require __DIR__ . '/auth.php';
