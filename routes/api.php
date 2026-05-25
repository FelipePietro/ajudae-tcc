<?php

use App\Http\Controllers\Api\PessoaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OngController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1/pessoas')->group(function () {
    Route::post('/register', [PessoaController::class, 'register']);
    Route::post('/login', [PessoaController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:pessoa'])->group(function () {
        Route::post('/logout', [PessoaController::class, 'logout']);
        Route::get('/me', [PessoaController::class, 'me']);
    });
});

Route::prefix('v1/ongs')->group(function () {
    Route::post('/register', [OngController::class, 'register']);
    Route::post('/login', [OngController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:ong'])->group(function () {
        Route::post('/logout', [OngController::class, 'logout']);
        Route::get('/me', [OngController::class, 'me']);
    });

Route::post(
    '/v1/pessoas/login',
    [PessoaController::class, 'login']
);

Route::post(
    '/v1/ongs/login',
    [OngController::class, 'login']
);
});