<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecursoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('recursos', RecursoController::class);

Route::apiResource('causas', RecursoController::class);

Route::apiResource('habilidades', RecursoController::class);

Route::apiResource('cat_eventos', RecursoController::class);
