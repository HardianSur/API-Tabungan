<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\RegisterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::controller(RegisterController::class)->group(function(){
    Route::post('auth/register', 'register');
    Route::post('auth/login', 'login');
});

Route::middleware('auth:sanctum')->group( function () {
    Route::resource('tabungan', TargetController::class);
    Route::get('tabungan/user/{id}', [TargetController::class, 'getAllTargetByUser']);
    Route::resource('tabungan/bayar', PayController::class);
    Route::get('auth/logout', [RegisterController::class, 'logout']);
});

// Route::apiResource('/tabungan', App\Http\Controllers\Api\TargetController::class);
// Route::get('/tabungan/user/{id}', [App\Http\Controllers\Api\TargetController::class, 'getAllTargetByUser']);
// Route::apiResource('/tabungan/bayar', App\Http\Controllers\Api\PayController::class);