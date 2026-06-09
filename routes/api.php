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

//--------------------------------PESSOA--------------------------------//
Route::prefix('v1/pessoas')->group(function () {
    Route::post('/register', [PessoaController::class, 'register']);
    Route::post('/login', [PessoaController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:pessoa'])->group(function () {
        Route::post('/logout', [PessoaController::class, 'logout']);
        Route::get('/me', [PessoaController::class, 'me']);
    });
});

//--------------------------------ONG--------------------------------//
Route::prefix('v1/ongs')->group(function () {
    Route::post('/register', [OngController::class, 'register']);
    Route::post('/login', [OngController::class, 'login']);

    Route::middleware(['auth:sanctum', 'abilities:ong'])->group(function () {
        Route::post('/logout', [OngController::class, 'logout']);
        Route::get('/me', [OngController::class, 'me']);
    });
});

//--------------------------------RECURSOS--------------------------------//
Route::prefix('v1/recursos')->group(function () {
    Route::get('/', [RecursoController::class, 'index']);
    Route::get('/{id}', [RecursoController::class, 'show']);

    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('/', [RecursoController::class, 'store']);
        Route::put('/{id}', [RecursoController::class, 'update']);
        Route::delete('/{id}', [RecursoController::class, 'destroy']);
    });
});

//--------------------------------CAUSAS--------------------------------//
route::prefix('v1/causas')->group(function () {
    Route::get('/', [CausaController::class, 'index']);
    Route::get('/{id}', [CausaController::class, 'show']);

    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('/', [CausaController::class, 'store']);
        Route::put('/{id}', [CausaController::class, 'update']);
        Route::delete('/{id}', [CausaController::class, 'destroy']);
    });
});

//--------------------------------HABILIDADES--------------------------------//
route::prefix('v1/habilidades')->group(function () {
    Route::get('/', [HabilidadeController::class, 'index']);
    Route::get('/{id}', [HabilidadeController::class, 'show']);

    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('/', [HabilidadeController::class, 'store']);
        Route::put('/{id}', [HabilidadeController::class, 'update']);
        Route::delete('/{id}', [HabilidadeController::class, 'destroy']);
    });
});

//--------------------------------CATEGORIAS DE EVENTO--------------------------------//
route::prefix('v1/categorias-evento')->group(function () {
    Route::get('/', [CatEventoController::class, 'index']);
    Route::get('/{id}', [CatEventoController::class, 'show']);

    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::post('/', [CatEventoController::class, 'store']);
        Route::put('/{id}', [CatEventoController::class, 'update']);
        Route::delete('/{id}', [CatEventoController::class, 'destroy']);
    });

    //--------------------------------AGENDAS--------------------------------//
Route::prefix('v1/agendas')->group(function () {
    Route::get('/', [AgendaController::class, 'index']);
    Route::get('/{id}', [AgendaController::class, 'show']);

    Route::middleware(['auth:sanctum', 'abilities:admin,ong'])->group(function () {
        Route::post('/', [AgendaController::class, 'store']);
        Route::put('/{id}', [AgendaController::class, 'update']);
        Route::delete('/{id}', [AgendaController::class, 'destroy']);
        
        // Rotas para alterar status
        Route::patch('/{id}/ativar', [AgendaController::class, 'ativar']);
        Route::patch('/{id}/finalizar', [AgendaController::class, 'finalizar']);
    });
});

