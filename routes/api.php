<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rotas abertas para todos os usuarios API
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
