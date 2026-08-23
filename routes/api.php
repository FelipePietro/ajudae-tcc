<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PessoaController;
use App\Http\Controllers\Api\OngController;
use App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\Api\RecursoController;
use App\Http\Controllers\Api\CausaController;
use App\Http\Controllers\Api\HabilidadeController;
use App\Http\Controllers\Api\CatEventoController;

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\EventoHabilidadeController;
use App\Http\Controllers\Api\InscricaoController;

use App\Http\Controllers\Api\PessoaHabilidadeController;
use App\Http\Controllers\Api\PessoaRecursoController;
use App\Http\Controllers\Api\PessoaCausaController;


/*
|--------------------------------------------------------------------------
| USER SANCTUM
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| PESSOAS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pessoas')->group(function () {

    // Públicas
    Route::post('/register', [PessoaController::class, 'register']);
    Route::post('/login', [PessoaController::class, 'login']);
    Route::get('/', [PessoaController::class, 'index']);

    // Autenticadas como Pessoa
    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa'
    ])->group(function () {

        Route::post('/logout', [PessoaController::class, 'logout']);
        Route::get('/me', [PessoaController::class, 'me']);

        Route::put('/{id}', [PessoaController::class, 'update']);
        Route::delete('/{id}', [PessoaController::class, 'destroy']);

        Route::patch(
            '/{id}/cancelar-exclusao',
            [PessoaController::class, 'cancelarExclusao']
        );
    });

    Route::get('/{id}', [PessoaController::class, 'show']);
});


/*
|--------------------------------------------------------------------------
| HABILIDADES DA PESSOA
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pessoas/{id}/habilidades')->group(function () {

    Route::get('/', [
        PessoaHabilidadeController::class,
        'index'
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa'
    ])->group(function () {

        Route::post('/', [
            PessoaHabilidadeController::class,
            'store'
        ]);

        Route::delete('/{hid}', [
            PessoaHabilidadeController::class,
            'destroy'
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| RECURSOS DA PESSOA
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pessoas/{id}/recursos')->group(function () {

    Route::get('/', [
        PessoaRecursoController::class,
        'index'
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa'
    ])->group(function () {

        Route::post('/', [
            PessoaRecursoController::class,
            'store'
        ]);

        Route::delete('/{rid}', [
            PessoaRecursoController::class,
            'destroy'
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| CAUSAS DA PESSOA
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pessoas/{id}/causas')->group(function () {

    Route::get('/', [
        PessoaCausaController::class,
        'index'
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa'
    ])->group(function () {

        Route::post('/', [
            PessoaCausaController::class,
            'store'
        ]);

        Route::delete('/{cid}', [
            PessoaCausaController::class,
            'destroy'
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| ONGS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/ongs')->group(function () {

    // Públicas
    Route::post('/register', [OngController::class, 'register']);
    Route::post('/login', [OngController::class, 'login']);
    Route::get('/', [OngController::class, 'index']);

    // ONG autenticada
    Route::middleware([
        'auth:sanctum',
        'abilities:ong'
    ])->group(function () {

        Route::post('/logout', [OngController::class, 'logout']);
        Route::get('/me', [OngController::class, 'me']);

        Route::put('/{id}', [OngController::class, 'update']);
        Route::delete('/{id}', [OngController::class, 'destroy']);

        Route::patch(
            '/{id}/cancelar-exclusao',
            [OngController::class, 'cancelarExclusao']
        );
    });

    // Admin aprova/reprova ONG
    Route::patch(
        '/{id}/status',
        [OngController::class, 'updateStatus']
    )->middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ]);

    Route::get('/{id}', [OngController::class, 'show']);
});


/*
|--------------------------------------------------------------------------
| RECURSOS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/recursos')->group(function () {

    Route::get('/', [RecursoController::class, 'index']);
    Route::get('/{id}', [RecursoController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ])->group(function () {

        Route::post('/', [RecursoController::class, 'store']);
        Route::put('/{id}', [RecursoController::class, 'update']);
        Route::delete('/{id}', [RecursoController::class, 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| CAUSAS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/causas')->group(function () {

    Route::get('/', [CausaController::class, 'index']);
    Route::get('/{id}', [CausaController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ])->group(function () {

        Route::post('/', [CausaController::class, 'store']);
        Route::put('/{id}', [CausaController::class, 'update']);
        Route::delete('/{id}', [CausaController::class, 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| HABILIDADES
|--------------------------------------------------------------------------
*/

Route::prefix('v1/habilidades')->group(function () {

    Route::get('/', [HabilidadeController::class, 'index']);
    Route::get('/{id}', [HabilidadeController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ])->group(function () {

        Route::post('/', [HabilidadeController::class, 'store']);
        Route::put('/{id}', [HabilidadeController::class, 'update']);
        Route::delete('/{id}', [HabilidadeController::class, 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| CATEGORIAS DE EVENTO
|--------------------------------------------------------------------------
*/

Route::prefix('v1/categorias-evento')->group(function () {

    Route::get('/', [CatEventoController::class, 'index']);
    Route::get('/{id}', [CatEventoController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ])->group(function () {

        Route::post('/', [CatEventoController::class, 'store']);
        Route::put('/{id}', [CatEventoController::class, 'update']);
        Route::delete('/{id}', [CatEventoController::class, 'destroy']);
    });
});


/*
|--------------------------------------------------------------------------
| EVENTOS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/eventos')->group(function () {

    // Públicas
    Route::get('/', [EventoController::class, 'index']);
    Route::get('/{id}', [EventoController::class, 'show']);
    Route::get('/eventos/{id}/habilidades', [EventoHabilidadeController::class, 'index']);

    // ONG
    Route::middleware([
        'auth:sanctum',
        'abilities:ong'
    ])->group(function () {

        Route::post('/', [EventoController::class, 'store']);
        Route::put('/{id}', [EventoController::class, 'update']);
        Route::delete('/{id}', [EventoController::class, 'destroy']);
        Route::post('/eventos/{id}/habilidades', [EventoHabilidadeController::class, 'store']);
        Route::delete('/eventos/{id}/habilidades/{hid}', [EventoHabilidadeController::class, 'destroy']);
    });

    // Admin
    Route::patch(
        '/{id}/status',
        [EventoController::class, 'updateStatus']
    )->middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin'
    ]);
});


/*
|--------------------------------------------------------------------------
| EVENTO POR ORGANIZADOR SOLO
|--------------------------------------------------------------------------
*/

Route::post(
    '/v1/pessoas/eventos',
    [EventoController::class, 'store']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa'
]);


/*
|--------------------------------------------------------------------------
| EVENTOS DE UMA ONG
|--------------------------------------------------------------------------
*/

Route::get(
    '/v1/ongs/{id}/eventos',
    [EventoController::class, 'porOng']
);


/*
|--------------------------------------------------------------------------
| AGENDAS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/agendas')->group(function () {

    Route::get('/', [AgendaController::class, 'index']);
    Route::get('/{id}', [AgendaController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'abilities:ong'
    ])->group(function () {

        Route::post('/', [AgendaController::class, 'store']);
        Route::put('/{id}', [AgendaController::class, 'update']);
        Route::delete('/{id}', [AgendaController::class, 'destroy']);

        Route::patch(
            '/{id}/ativar',
            [AgendaController::class, 'ativar']
        );

        Route::patch(
            '/{id}/finalizar',
            [AgendaController::class, 'finalizar']
        );
    });
});


/*
|--------------------------------------------------------------------------
| INSCRIÇÕES
|--------------------------------------------------------------------------
*/

// Pessoa se candidata a uma agenda
Route::post(
    '/v1/agendas/{id}/inscrever',
    [InscricaoController::class, 'store']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa'
]);


// Lista as inscrições da própria Pessoa
Route::get(
    '/v1/pessoas/{id}/inscricoes',
    [InscricaoController::class, 'porPessoa']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa'
]);


// Pessoa cancela a própria inscrição
Route::delete(
    '/v1/inscricoes/{id}',
    [InscricaoController::class, 'destroy']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa'
]);


// ONG ou organizador responsável gerencia candidatos.
// A autorização específica acontece dentro do Controller.
Route::middleware([
    'auth:sanctum'
])->group(function () {

    Route::get(
        '/v1/agendas/{id}/inscritos',
        [InscricaoController::class, 'index']
    );

    Route::patch(
        '/v1/inscricoes/{id}/status',
        [InscricaoController::class, 'updateStatus']
    );
});