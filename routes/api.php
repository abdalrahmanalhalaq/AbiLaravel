<?php

use App\Http\Controllers\Auth\AdminAbiController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

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


Route::apiResource('categories' , CategoryController::class)->middleware('auth:admins-api');


Route::prefix('auth')->group(function(){
   Route::post('register' , [AdminAbiController::class , 'register']);
   Route::post('login' , [AdminAbiController::class , 'login']);
});

Route::prefix('auth')->middleware('auth:admins-api')->group(function(){
    Route::get('logout' , [AdminAbiController::class, 'logout']);
    Route::put('changePassword' , [AdminAbiController::class, 'changePassword']);
});

Route::prefix('auth')->group(function(){
    Route::post('forgetPassword' , [AdminAbiController::class , 'forgetPassowrd'])->middleware('throttle:3,1');
    Route::put('resetPassword' , [AdminAbiController::class , 'resetPassword'])->middleware('throttle:3,1');

});

Route::middleware('auth:admins-api')->group(function(){
    Route::apiResource('role' , RoleController::class);
    Route::apiResource('permission' , PermissionController::class);

    Route::put('role/{role}/permission/{permission}', [RoleController::class  , 'UpdateRolePermission']);
});
