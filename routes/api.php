<?php

use App\Http\Controllers\Api\ContaAPagarApiController;
use App\Http\Controllers\Api\AnalyticsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// API Externa - Contas a Pagar
Route::post('contas-a-pagar', [ContaAPagarApiController::class, 'store']);

// API Externa - Analytics
Route::get('cmv', [AnalyticsApiController::class, 'cmv']);
Route::get('cmv-global', [AnalyticsApiController::class, 'cmvGlobal']);
Route::get('aproveitamento', [AnalyticsApiController::class, 'aproveitamento']);
