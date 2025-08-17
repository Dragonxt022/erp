<?php

use App\Http\Controllers\Api\ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Api de Autentificação
Route::post('login-pin', [ApiAuthController::class, 'loginComPin']);
Route::post('login', [ApiAuthController::class, 'login']);
Route::post('logout', [ApiAuthController::class, 'logout']);
Route::get('profile', [ApiAuthController::class, 'getProfile'])->middleware('auth:sanctum');
