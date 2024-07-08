<?php

use App\Http\Controllers\web\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth:api');

Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'view'])->name('login');
    Route::post('/login/signin', [AuthController::class, 'signin']);
});
