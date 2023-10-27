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

// AUTHENTICATION
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

// USER
Route::group(['middleware'=>['api', 'isAdmin'], 'prefix'=>'admin'], function ($router) {
   Route::get('/users', [\App\Http\Controllers\UserController::class, 'getAll']);
});

//CATEGORY PRODUCT - public
Route::group(['middleware'=>'api', 'prefix'=>'category-product'], function ($router){
    Route::get('/getAll', [\App\Http\Controllers\API\CategoryProductController::class, 'getAll']);
    Route::get('/slug={slug}', [\App\Http\Controllers\API\CategoryProductController::class, 'show']);
    Route::get('/filter', [\App\Http\Controllers\API\CategoryProductController::class, 'filter']);
    Route::get('/search' , [\App\Http\Controllers\API\CategoryProductController::class, 'searchByName']);
});


//CATEGORY PRODUCT - admin
Route::group(['middleware'=>['api', 'isAdmin'], 'prefix'=>'category-product'], function ($router){
    Route::post('/store', [\App\Http\Controllers\API\CategoryProductController::class, 'store']);
    Route::post('/edit/{id}', [\App\Http\Controllers\API\CategoryProductController::class, 'edit']);
    Route::post('/destroy/{id}' , [\App\Http\Controllers\API\CategoryProductController::class, 'destroy']);
    Route::post('/in-available/{id}', [\App\Http\Controllers\API\CategoryProductController::class, 'inAvailable']);
    Route::post('/restore/{id}', [\App\Http\Controllers\API\CategoryProductController::class, 'restore']);
});


//PRODUCT
Route::apiResource('product', \App\Http\Controllers\API\ProductController::class);
