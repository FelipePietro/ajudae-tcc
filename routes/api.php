<?php

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\AdminPadrinhoController;
use App\Http\Controllers\Api\AntecedentesController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\AdminPessoaValidationController;
use App\Http\Controllers\Api\AdminOrganizadorController;
use App\Http\Controllers\Api\AssinaturaController;
use App\Http\Controllers\Api\CampanhaXpController;
use App\Http\Controllers\Api\CatEventoController;
use App\Http\Controllers\Api\CertificadoController;
use App\Http\Controllers\Api\CausaController;
use App\Http\Controllers\Api\ConfiabilidadeController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\EventoAssociacaoController;
use App\Http\Controllers\Api\EventoHabilidadeController;
use App\Http\Controllers\Api\DocumentoPessoaController;
use App\Http\Controllers\Api\HabilidadeController;
use App\Http\Controllers\Api\ManagedImageController;
use App\Http\Controllers\Api\InscricaoController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OngController;
use App\Http\Controllers\Api\OngOrganizadorController;
use App\Http\Controllers\Api\PadrinhoController;
use App\Http\Controllers\Api\OrganizadorController;
use App\Http\Controllers\Api\PessoaCausaController;
use App\Http\Controllers\Api\PessoaController;
use App\Http\Controllers\Api\PessoaHabilidadeController;
use App\Http\Controllers\Api\PessoaRecursoController;
use App\Http\Controllers\Api\PessoaValidationController;
use App\Http\Controllers\Api\RecursoController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\SolicitacaoLgpdController;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| USER SANCTUM
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PESSOAS
|--------------------------------------------------------------------------
*/

Route::prefix('v1/pessoas')->group(function () {

    // Públicas
    Route::post('/register', [PessoaController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [PessoaController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/', [PessoaController::class, 'index']);
    Route::get('/{id}/imagem', [ManagedImageController::class, 'pessoaShow']);

    // Autenticadas como Pessoa
    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
    ])->group(function () {

        Route::post('/logout', [PessoaController::class, 'logout']);
        Route::get('/me', [PessoaController::class, 'me']);
        Route::post('/{id}/imagem', [ManagedImageController::class, 'pessoaUpload']);
        Route::delete('/{id}/imagem', [ManagedImageController::class, 'pessoaRemove']);
        Route::get('/{pessoa}/confiabilidade', [ConfiabilidadeController::class, 'show']);
        Route::get('/me/validacao', [PessoaValidationController::class, 'show']);
        Route::get('/me/antecedentes', [AntecedentesController::class, 'meus']);
        Route::post('/me/validacao/reenviar', [PessoaValidationController::class, 'reenviar']);
        Route::post('/me/documentos/{tipo}', [DocumentoPessoaController::class, 'store']);
        Route::post('/me/solicitacao-organizador', [OrganizadorController::class, 'solicitar']);
        Route::get('/me/solicitacao-organizador', [OrganizadorController::class, 'status']);
        Route::get('/me/convites-ong', [OrganizadorController::class, 'convites']);
        Route::post('/me/convites-ong/{vinculo}/aceitar', [OrganizadorController::class, 'aceitarConvite']);
        Route::post('/me/convites-ong/{vinculo}/recusar', [OrganizadorController::class, 'recusarConvite']);
        Route::delete('/me/vinculos-ong/{vinculo}', [OrganizadorController::class, 'encerrarVinculo']);

        Route::match(['put', 'patch'], '/{id}', [PessoaController::class, 'update']);
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
        'index',
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
    ])->group(function () {

        Route::post('/', [
            PessoaHabilidadeController::class,
            'store',
        ]);

        Route::delete('/{hid}', [
            PessoaHabilidadeController::class,
            'destroy',
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
        'index',
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
    ])->group(function () {

        Route::post('/', [
            PessoaRecursoController::class,
            'store',
        ]);

        Route::delete('/{rid}', [
            PessoaRecursoController::class,
            'destroy',
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
        'index',
    ]);

    Route::middleware([
        'auth:sanctum',
        'abilities:pessoa',
    ])->group(function () {

        Route::post('/', [
            PessoaCausaController::class,
            'store',
        ]);

        Route::delete('/{cid}', [
            PessoaCausaController::class,
            'destroy',
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
    Route::post('/register', [OngController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [OngController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/', [OngController::class, 'index']);
    Route::get('/{id}/imagem', [ManagedImageController::class, 'ongShow']);

    // ONG autenticada
    Route::middleware([
        'auth:sanctum',
        'abilities:ong',
    ])->group(function () {

        Route::post('/logout', [OngController::class, 'logout']);
        Route::get('/me', [OngController::class, 'me']);
        Route::post('/{id}/imagem', [ManagedImageController::class, 'ongUpload']);
        Route::delete('/{id}/imagem', [ManagedImageController::class, 'ongRemove']);
        Route::post('/me/convites-organizador', [OngOrganizadorController::class, 'convidar']);
        Route::get('/me/organizadores', [OngOrganizadorController::class, 'index']);
        Route::delete('/me/organizadores/{vinculo}', [OngOrganizadorController::class, 'encerrar']);
        Route::patch('/me/eventos/{evento}/responsavel', [OngOrganizadorController::class, 'atribuirResponsavel']);

        Route::match(['put', 'patch'], '/{id}', [OngController::class, 'update']);
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
        'admin:ongs.validar',
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
        'admin',
    ])->group(function () {

        Route::post('/', [RecursoController::class, 'store']);
        Route::match(['put', 'patch'], '/{id}', [RecursoController::class, 'update']);
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
        'admin',
    ])->group(function () {

        Route::post('/', [CausaController::class, 'store']);
        Route::match(['put', 'patch'], '/{id}', [CausaController::class, 'update']);
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
        'admin',
    ])->group(function () {

        Route::post('/', [HabilidadeController::class, 'store']);
        Route::match(['put', 'patch'], '/{id}', [HabilidadeController::class, 'update']);
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
        'admin',
    ])->group(function () {

        Route::post('/', [CatEventoController::class, 'store']);
        Route::match(['put', 'patch'], '/{id}', [CatEventoController::class, 'update']);
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
    Route::get('/{id}/imagem', [ManagedImageController::class, 'eventoShow']);
    Route::get('/{id}', [EventoController::class, 'show']);
    Route::get('/{id}/habilidades', [EventoHabilidadeController::class, 'index']);
    Route::get('/{id}/causas', [EventoAssociacaoController::class, 'causas']);
    Route::get('/{id}/recursos', [EventoAssociacaoController::class, 'recursos']);

    // ONG aprovada ou Pessoa organizadora
    Route::middleware([
        'auth:sanctum',
        'ability:ong,pessoa',
        'ong.approved',
    ])->group(function () {

        Route::post('/', [EventoController::class, 'store']);
        Route::post('/{id}/imagem', [ManagedImageController::class, 'eventoUpload']);
        Route::delete('/{id}/imagem', [ManagedImageController::class, 'eventoRemove']);
        Route::match(['put', 'patch'], '/{id}', [EventoController::class, 'update']);
        Route::delete('/{id}', [EventoController::class, 'destroy']);
        Route::post('/{id}/cancelar', [EventoController::class, 'cancelar']);
        Route::post('/{id}/finalizar', [EventoController::class, 'finalizar']);
        Route::post('/{id}/avaliacoes/{pessoa}', [AvaliacaoController::class, 'voluntario']);
        Route::post('/{id}/habilidades', [EventoHabilidadeController::class, 'store']);
        Route::delete('/{id}/habilidades/{hid}', [EventoHabilidadeController::class, 'destroy']);
        Route::post('/{id}/causas', [EventoAssociacaoController::class, 'adicionarCausa']);
        Route::delete('/{id}/causas/{causa}', [EventoAssociacaoController::class, 'removerCausa']);
        Route::post('/{id}/recursos', [EventoAssociacaoController::class, 'adicionarRecurso']);
        Route::delete('/{id}/recursos/{recurso}', [EventoAssociacaoController::class, 'removerRecurso']);
    });

    Route::post('/{id}/avaliacao-ong', [AvaliacaoController::class, 'ong'])
        ->middleware(['auth:sanctum', 'abilities:pessoa']);

    Route::post('/{id}/certificado', [CertificadoController::class, 'emitir'])
        ->middleware(['auth:sanctum', 'abilities:pessoa']);
    Route::get('/{id}/certificado', [CertificadoController::class, 'show'])
        ->middleware(['auth:sanctum', 'abilities:pessoa']);

    // Admin
    Route::patch(
        '/{id}/status',
        [EventoController::class, 'updateStatus']
    )->middleware([
        'auth:sanctum',
        'abilities:pessoa',
        'admin:eventos.aprovar',
    ]);
    Route::get('/admin/pendentes', [EventoController::class, 'pendentes'])->middleware([
        'auth:sanctum', 'abilities:pessoa', 'admin:eventos.aprovar',
    ]);
});

Route::get('/v1/certificados/verificar/{codigo}', [CertificadoController::class, 'verificar']);
Route::get('/v1/certificados/{certificado}/download', [CertificadoController::class, 'download'])
    ->middleware(['auth:sanctum', 'abilities:pessoa']);

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
    'abilities:pessoa',
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

Route::get('/v1/eventos/{evento}/agendas', [AgendaController::class, 'porEvento']);
Route::post('/v1/eventos/{evento}/agendas', [AgendaController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:ong,pessoa', 'ong.approved']);

Route::prefix('v1/agendas')->group(function () {

    Route::get('/', [AgendaController::class, 'index']);
    Route::get('/{id}', [AgendaController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'ability:ong,pessoa',
        'ong.approved',
    ])->group(function () {

        Route::post('/', [AgendaController::class, 'store']);
        Route::match(['put', 'patch'], '/{id}', [AgendaController::class, 'update']);
        Route::delete('/{id}', [AgendaController::class, 'destroy']);
        Route::post('/{id}/cancelar', [AgendaController::class, 'cancelar']);

        Route::patch(
            '/{id}/ativar',
            [AgendaController::class, 'ativar']
        );

        Route::patch(
            '/{id}/finalizar',
            [AgendaController::class, 'finalizar']
        );
        Route::post('/{id}/consolidar-xp', [AgendaController::class, 'consolidarXp']);
    });
});

Route::get('/v1/ranking', [RankingController::class, 'index']);

Route::prefix('v1/admin/campanhas-xp')->middleware([
    'auth:sanctum',
    'abilities:pessoa',
    'admin:xp.campanhas.gerenciar',
])->group(function () {
    Route::get('/', [CampanhaXpController::class, 'index']);
    Route::post('/', [CampanhaXpController::class, 'store']);
    Route::patch('/{campanha}', [CampanhaXpController::class, 'update']);
    Route::patch('/{campanha}/status', [CampanhaXpController::class, 'status']);
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
    'abilities:pessoa',
]);

Route::post('/v1/agendas/{id}/inscricoes', [InscricaoController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:pessoa']);

Route::get('/v1/minhas-inscricoes', [InscricaoController::class, 'minhas'])
    ->middleware(['auth:sanctum', 'abilities:pessoa']);

// Lista as inscrições da própria Pessoa
Route::get(
    '/v1/pessoas/{id}/inscricoes',
    [InscricaoController::class, 'porPessoa']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa',
]);

// Pessoa cancela a própria inscrição
Route::delete(
    '/v1/inscricoes/{id}',
    [InscricaoController::class, 'destroy']
)->middleware([
    'auth:sanctum',
    'abilities:pessoa',
]);

// ONG ou organizador responsável gerencia candidatos.
// A autorização específica acontece dentro do Controller.
Route::middleware([
    'auth:sanctum',
])->group(function () {

    Route::get('/v1/inscricoes/{id}', [InscricaoController::class, 'show']);

    Route::get(
        '/v1/agendas/{id}/inscritos',
        [InscricaoController::class, 'index']
    );

    Route::patch(
        '/v1/inscricoes/{id}/status',
        [InscricaoController::class, 'updateStatus']
    );

    Route::patch('/v1/inscricoes/{id}/presenca', [InscricaoController::class, 'presenca']);
});

/*
|--------------------------------------------------------------------------
| NOTIFICAÇÕES, TERMOS E LGPD
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'ability:pessoa,ong'])->group(function () {
    Route::get('/v1/documentos/{documento}/download', [DocumentoPessoaController::class, 'download']);

    Route::get('/v1/notificacoes', [NotificationController::class, 'index']);
    Route::get('/v1/notificacoes/{id}', [NotificationController::class, 'show']);
    Route::patch('/v1/notificacoes/{id}/lida', [NotificationController::class, 'markAsRead']);
    Route::delete('/v1/notificacoes/{id}', [NotificationController::class, 'destroy']);

    Route::get('/v1/assinaturas', [AssinaturaController::class, 'index']);
    Route::post('/v1/assinaturas', [AssinaturaController::class, 'store']);
    Route::get('/v1/assinaturas/{assinatura}', [AssinaturaController::class, 'show']);

    Route::post('/v1/solicitacoes-lgpd', [SolicitacaoLgpdController::class, 'store']);
    Route::get('/v1/minhas-solicitacoes-lgpd', [SolicitacaoLgpdController::class, 'minhas']);
});

Route::middleware(['auth:sanctum', 'abilities:pessoa', 'admin:lgpd.visualizar'])->group(function () {
    Route::get('/v1/admin/solicitacoes-lgpd', [SolicitacaoLgpdController::class, 'index']);
    Route::patch('/v1/admin/solicitacoes-lgpd/{id}/status', [SolicitacaoLgpdController::class, 'updateStatus']);
});

Route::prefix('v1/admin')->middleware([
    'auth:sanctum',
    'abilities:pessoa',
    'admin:usuarios.validar',
])->group(function () {
    Route::get('/validacoes-pessoa', [AdminPessoaValidationController::class, 'index']);
    Route::get('/validacoes-pessoa/{pessoa}', [AdminPessoaValidationController::class, 'show']);
    Route::post('/validacoes-pessoa/{pessoa}/assumir', [AdminPessoaValidationController::class, 'assumir']);
    Route::post('/validacoes-pessoa/{pessoa}/solicitar-correcao', [AdminPessoaValidationController::class, 'solicitarCorrecao']);
    Route::post('/validacoes-pessoa/{pessoa}/aprovar', [AdminPessoaValidationController::class, 'aprovar']);
    Route::post('/validacoes-pessoa/{pessoa}/reprovar', [AdminPessoaValidationController::class, 'reprovar']);
    Route::post('/validacoes-pessoa/{pessoa}/reprovar-definitivamente', [AdminPessoaValidationController::class, 'reprovarDefinitivamente']);
    Route::patch('/documentos/{documento}/status', [AdminPessoaValidationController::class, 'validarDocumento']);
    Route::get('/antecedentes/renovacoes', [AntecedentesController::class, 'renovacoes']);
});

Route::prefix('v1/padrinhos')->middleware(['auth:sanctum', 'abilities:pessoa'])->group(function () {
    Route::get('/elegiveis', [PadrinhoController::class, 'elegiveis']);
    Route::get('/me', [PadrinhoController::class, 'meus']);
    Route::get('/recebidas', [PadrinhoController::class, 'recebidas']);
    Route::post('/solicitacoes', [PadrinhoController::class, 'solicitar']);
    Route::post('/solicitacoes/{pedido}/aceitar', [PadrinhoController::class, 'aceitar']);
    Route::post('/solicitacoes/{pedido}/recusar', [PadrinhoController::class, 'recusar']);
    Route::post('/solicitacoes/{pedido}/cancelar', [PadrinhoController::class, 'cancelar']);
    Route::get('/vinculos/{vinculo}', [PadrinhoController::class, 'vinculo']);
    Route::post('/vinculos/{vinculo}/encerrar', [PadrinhoController::class, 'encerrar']);
    Route::post('/vinculos/{vinculo}/solicitar-conclusao', [PadrinhoController::class, 'solicitarConclusao']);
});

Route::prefix('v1/admin/padrinhos/conclusoes')->middleware([
    'auth:sanctum', 'abilities:pessoa', 'admin:padrinhos.validar_conclusao',
])->group(function () {
    Route::get('/', [AdminPadrinhoController::class, 'conclusoes']);
    Route::post('/{pedido}/aprovar', [AdminPadrinhoController::class, 'aprovar']);
    Route::post('/{pedido}/reprovar', [AdminPadrinhoController::class, 'reprovar']);
});

Route::prefix('v1/admin/solicitacoes-organizador')->middleware([
    'auth:sanctum',
    'abilities:pessoa',
    'admin:organizadores.validar',
])->group(function () {
    Route::get('/', [AdminOrganizadorController::class, 'index']);
    Route::get('/{solicitacao}', [AdminOrganizadorController::class, 'show']);
    Route::post('/{solicitacao}/iniciar-analise', [AdminOrganizadorController::class, 'iniciarAnalise']);
    Route::post('/{solicitacao}/aprovar', [AdminOrganizadorController::class, 'aprovar']);
    Route::post('/{solicitacao}/reprovar', [AdminOrganizadorController::class, 'reprovar']);
});

Route::get('/health', fn () => ApiResponse::success(['status' => 'ok'], 'API online.'));

// ##### ROTAS TEMPORÁRIAS ######

Route::get('/ong/eventos/{id}/candidatos', function ($id) {

    /*
     * Depois substituímos pelo Evento::findOrFail($id)
     * e pelas candidaturas reais.
     */

    return redirect()->route(
        'ong.candidatos',
        [
            'evento' => $id,
        ]
    );

})->name('ong.eventos.candidatos');

Route::patch('/ong/candidatos/{id}/aprovar', function ($id) {

    return back()->with(
        'success',
        'Candidatura aprovada com sucesso.'
    );

})->name('ong.candidatos.aprovar');

Route::patch('/ong/candidatos/{id}/recusar', function ($id) {

    return back()->with(
        'success',
        'Candidatura recusada.'
    );

})->name('ong.candidatos.recusar');

Route::post('/ong/candidatos/aprovar-lote', function (Request $request) {

    return back()->with(
        'success',
        'Candidaturas selecionadas aprovadas.'
    );

})->name('ong.candidatos.aprovar-lote');

// ##### ROTAS TEMPORÁRIAS ######
