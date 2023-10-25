<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware'=>'api', 'prefix'=>'auth'], function ($router){
   Route::post('/register', [AuthController::class , 'register'] );
   Route::post('/login', [AuthController::class, 'login']);
   Route::get('/profile', [AuthController::class, 'profile']);
   Route::post('/logout', [AuthController::class, 'logout']);
   Route::post('/verify-account/{token}' , [AuthController::class, 'verifyAccount']);
   Route::post('/create-otp', [AuthController::class , 'createOTP']);
   Route::post('/check-otp', [AuthController::class, 'checkOTP']);
   Route::post('/reset-password', [AuthController::class, 'resetPassword']);
   Route::get('/go-to-change-password', [AuthController::class, 'goToChangePassword']);
   Route::post('/refresh', [AuthController::class, 'refresh']);

});

Route::group(['middleware'=>['api', 'isAdmin'], 'prefix'=>'admin'], function ($router) {
   Route::get('/users', [\App\Http\Controllers\UserController::class, 'getAll']);

});


Route::apiResource('category_products', \App\Http\Controllers\API\CategoryProductController::class);


Route::group(['middleware'=>'api', 'prefix'=>'category-products'], function ($router){
    Route::get('/getAll', [\App\Http\Controllers\API\CategoryProductController::class, 'index']);
});
