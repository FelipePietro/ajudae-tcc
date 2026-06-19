<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PessoaController;
use App\Http\Controllers\Api\OngController;
use App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\RecursoController;
use App\Http\Controllers\CausaController;
use App\Http\Controllers\HabilidadeController;
use App\Http\Controllers\CatEventoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Pessoa
Route::prefix('v1/pessoas')->group(function () {
    Route::post('/register', [PessoaController::class, 'register']);
    Route::post('/login', [PessoaController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:pessoa'])->group(function () {
        Route::post('/logout', [PessoaController::class, 'logout']);
        Route::get('/me', [PessoaController::class, 'me']);
    });
});

// Ong
Route::prefix('v1/ongs')->group(function () {
    Route::post('/register', [OngController::class, 'register']);
    Route::post('/login', [OngController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:ong'])->group(function () {
        Route::post('/logout', [OngController::class, 'logout']);
        Route::get('/me', [OngController::class, 'me']);
    });
});

// Recursos
Route::controller(RecursoController::class)->prefix('recursos')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// Causas
Route::controller(CausaController::class)->prefix('causas')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// Habilidades
Route::controller(HabilidadeController::class)->prefix('habilidades')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// Categorias de Evento
Route::controller(CatEventoController::class)->prefix('cat_eventos')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'destroy');
});

// Notificações
Route::get('/notifications', [NotificationController::class, 'index']);
Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);