<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\CausaController;
use App\Http\Controllers\HabilidadeController;
use App\Http\Controllers\CatEventoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('recursos', RecursoController::class);

Route::apiResource('causas', CausaController::class);

Route::apiResource('habilidades', HabilidadeController::class);

Route::apiResource('cat_eventos', CatEventoController::class);
