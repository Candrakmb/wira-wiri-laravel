<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\MidtransApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProfilApiController;
use App\Http\Controllers\Api\RegisterApiController;
use App\Http\Controllers\Api\WpApiController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['middleware' => ['auth:api']], function() {
    Route::get('/user' , [ProfilApiController::class,'get_profil']);
    Route::prefix('profil')->name('menu.')->group(function(){
        Route::post('update' , [ProfilApiController::class,'update_profil']);
        Route::get('alamat' , [ProfilApiController::class,'getAlamat']);
        Route::get('detail/alamat/{id}' , [ProfilApiController::class,'getAlamatUpdate']);
        Route::post('create/alamat' , [ProfilApiController::class,'createAlamat']);
        Route::post('update/alamat' , [ProfilApiController::class,'updateAlamat']);
        Route::delete('delete/alamat/{id}' , [ProfilApiController::class,'deleteAlamat']);
        Route::post('/status/{status}',[ProfilApiController::class,'statusOnOff']);
        Route::get('status' , [ProfilApiController::class,'statusDriverKedai']);
    });
    Route::prefix('menu')->name('menu.')->group(function(){
        Route::post('/kedai' , [MenuApiController::class,'get_kedai']);
        Route::get('/get/{id}',[MenuApiController::class,'get_menu']);
        Route::get('/create',[MenuApiController::class,'create']);
        Route::get('/update/{id}',[MenuApiController::class,'update']);
        Route::post('/createform',[MenuApiController::class,'createform']);
        Route::post('/updateform',[MenuApiController::class,'updateform']);
        Route::delete('/deleteform/{id}',[MenuApiController::class,'deleteform']);
    });

    Route::prefix('order')->name('order.')->group(function(){
        Route::get('/{invoice}',[OrderApiController::class,'data_order']);
        Route::post('/create',[OrderApiController::class,'create_order']);
    });
    Route::prefix('wp')->name('wp.')->group(function(){
        Route::get('/{invoice}',[WpApiController::class,'weightProduct']);
    });
    Route::post('/logout' , [AuthApiController::class,'logout']);
   
});

Route::post('/register', RegisterApiController::class);
Route::post('/login' , [AuthApiController::class,'login']);
Route::post('/payments', [MidtransApiController::class, 'test']);


