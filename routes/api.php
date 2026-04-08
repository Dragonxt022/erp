<?php

use App\Http\Controllers\Api\ContaAPagarApiController;
use App\Http\Controllers\Api\EventoProcessadoApiController;
use App\Http\Controllers\Api\AnalyticsApiController;
use App\Http\Controllers\Api\SalmaoHistoricoApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// API Externa - Contas a Pagar e Analytics (Protegidas pelo SSO)
Route::middleware(['auth.sso'])->group(function () {
    Route::post('contas-a-pagar', [ContaAPagarApiController::class, 'store']);
    Route::post('salmao-historico', [SalmaoHistoricoApiController::class, 'store']);

    // API Externa - Analytics
    Route::get('cmv', [AnalyticsApiController::class, 'cmv']);
    Route::get('cmv-global', [AnalyticsApiController::class, 'cmvGlobal']);
    Route::get('aproveitamento', [AnalyticsApiController::class, 'aproveitamento']);
});

Route::prefix('broker')->group(function () {
    Route::get('heartbeat', [EventoProcessadoApiController::class, 'heartbeat']);
    Route::post('eventos-processados/check', [EventoProcessadoApiController::class, 'check']);
    Route::post('eventos-processados', [EventoProcessadoApiController::class, 'store']);
    Route::get('eventos-processados/{deliveryId}', [EventoProcessadoApiController::class, 'show']);
    Route::put('eventos-processados/processando', [EventoProcessadoApiController::class, 'marcarProcessando']);
    Route::put('eventos-processados/confirmar', [EventoProcessadoApiController::class, 'confirmar']);
});

Route::prefix('delivery')->group(function () {
    Route::put('processando', [EventoProcessadoApiController::class, 'marcarProcessando']);
    Route::put('ok', [EventoProcessadoApiController::class, 'confirmar']);
});
