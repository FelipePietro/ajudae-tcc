<?php

use App\Http\Controllers\Api\AdminOrganizadorController;
use App\Http\Controllers\Api\AdminPadrinhoController;
use App\Http\Controllers\Api\AdminPessoaValidationController;
use App\Http\Controllers\Api\AntecedentesController;
use App\Http\Controllers\Api\CampanhaXpController;
use App\Http\Controllers\Api\CatEventoController;
use App\Http\Controllers\Api\CausaController;
use App\Http\Controllers\Api\ConfiabilidadeController;
use App\Http\Controllers\Api\DocumentoPessoaController;
use App\Http\Controllers\Api\HabilidadeController;
use App\Http\Controllers\Api\ManagedImageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OngController;
use App\Http\Controllers\Api\OrganizadorController;
use App\Http\Controllers\Api\PadrinhoController;
use App\Http\Controllers\Api\PessoaCausaController;
use App\Http\Controllers\Api\PessoaController;
use App\Http\Controllers\Api\PessoaHabilidadeController;
use App\Http\Controllers\Api\PessoaRecursoController;
use App\Http\Controllers\Api\PessoaValidationController;
use App\Http\Controllers\Api\RecursoController;
use App\Http\Controllers\Api\SolicitacaoLgpdController;
use App\Http\Controllers\WebAccountDataController;
use App\Http\Controllers\WebOngRegistrationController;
use App\Http\Controllers\WebOrganizerLinksController;
use App\Http\Middleware\ResolveWebAccount;
use App\Http\Middleware\UseResolvedWebActor;
use Illuminate\Support\Facades\Route;

Route::middleware(ResolveWebAccount::class.':public')->group(function (): void {
    Route::post('/cadastro/ong', [WebOngRegistrationController::class, 'store'])
        ->middleware('throttle:5,1')->name('identity.ong.registration')->block();
    Route::prefix('catalogos')->group(function (): void {
        Route::get('/habilidades', [HabilidadeController::class, 'index']);
        Route::get('/recursos', [RecursoController::class, 'index']);
        Route::get('/causas', [CausaController::class, 'index']);
        Route::get('/categorias-evento', [CatEventoController::class, 'index']);
    });
});

Route::middleware([ResolveWebAccount::class.':required', UseResolvedWebActor::class])->group(function (): void {
    Route::get('/conta/ong/{ong}/documento-identidade', [WebOngRegistrationController::class, 'document'])
        ->whereNumber('ong')->name('identity.ong.document')->block();
    Route::get('/conta/documentos/{documento}/download', [DocumentoPessoaController::class, 'download'])->whereNumber('documento')->block();
    Route::get('/conta/notificacoes/{id}', [NotificationController::class, 'show'])->whereNumber('id')->block();
    Route::delete('/conta/notificacoes/{id}', [NotificationController::class, 'destroy'])->whereNumber('id')->block();
});

Route::middleware([ResolveWebAccount::class.':ong', UseResolvedWebActor::class])->prefix('conta/ong')->group(function (): void {
    Route::get('/dados', [OngController::class, 'me'])->block();
    Route::patch('/{id}/dados', [OngController::class, 'update'])->whereNumber('id')->block();
    Route::post('/{id}/imagem', [ManagedImageController::class, 'ongUpload'])->whereNumber('id')->block();
    Route::delete('/{id}/imagem', [ManagedImageController::class, 'ongRemove'])->whereNumber('id')->block();
    Route::patch('/eventos/{evento}/responsavel', [WebOrganizerLinksController::class, 'assign'])->whereNumber('evento')->block();
});

Route::middleware([ResolveWebAccount::class.':jornada-pessoa', UseResolvedWebActor::class])->prefix('conta/pessoa')->group(function (): void {
    Route::get('/dados', [PessoaController::class, 'me'])->block();
    Route::patch('/{id}/dados', [PessoaController::class, 'update'])->whereNumber('id')->block();
    Route::post('/{id}/imagem', [ManagedImageController::class, 'pessoaUpload'])->whereNumber('id')->block();
    Route::delete('/{id}/imagem', [ManagedImageController::class, 'pessoaRemove'])->whereNumber('id')->block();
    Route::get('/validacao', [PessoaValidationController::class, 'show'])->block();
    Route::post('/validacao/reenviar', [PessoaValidationController::class, 'reenviar'])->block();
    Route::get('/antecedentes', [AntecedentesController::class, 'meus'])->block();
    Route::post('/documentos/{tipo}', [DocumentoPessoaController::class, 'store'])->block();
    Route::get('/preferencias', [WebAccountDataController::class, 'preferences'])->block();
    Route::post('/{id}/habilidades', [PessoaHabilidadeController::class, 'store'])->whereNumber('id')->block();
    Route::delete('/{id}/habilidades/{hid}', [PessoaHabilidadeController::class, 'destroy'])->whereNumber(['id', 'hid'])->block();
    Route::post('/{id}/recursos', [PessoaRecursoController::class, 'store'])->whereNumber('id')->block();
    Route::delete('/{id}/recursos/{rid}', [PessoaRecursoController::class, 'destroy'])->whereNumber(['id', 'rid'])->block();
    Route::post('/{id}/causas', [PessoaCausaController::class, 'store'])->whereNumber('id')->block();
    Route::delete('/{id}/causas/{cid}', [PessoaCausaController::class, 'destroy'])->whereNumber(['id', 'cid'])->block();
    Route::get('/{pessoa}/confiabilidade', [ConfiabilidadeController::class, 'show'])->whereNumber('pessoa')->block();
    Route::get('/solicitacao-organizador', [OrganizadorController::class, 'status'])->block();
    Route::post('/solicitacao-organizador', [OrganizadorController::class, 'solicitar'])->block();

    Route::prefix('padrinhos')->controller(PadrinhoController::class)->group(function (): void {
        Route::get('/me', 'meus')->block();
        Route::get('/recebidas', 'recebidas')->block();
        Route::get('/codigo', 'codigo')->block();
        Route::post('/codigo', 'emitirCodigo')->block();
        Route::delete('/codigo', 'revogarCodigo')->block();
        Route::post('/indicacao', 'indicar')->middleware('throttle:5,1')->block();
        Route::get('/elegiveis', 'elegiveis')->block();
        Route::post('/solicitacoes', 'solicitar')->block();
        Route::post('/solicitacoes/{pedido}/aceitar', 'aceitar')->whereNumber('pedido')->block();
        Route::post('/solicitacoes/{pedido}/recusar', 'recusar')->whereNumber('pedido')->block();
        Route::post('/solicitacoes/{pedido}/cancelar', 'cancelar')->whereNumber('pedido')->block();
        Route::get('/vinculos/{vinculo}', 'vinculo')->whereNumber('vinculo')->block();
        Route::post('/vinculos/{vinculo}/encerrar', 'encerrar')->whereNumber('vinculo')->block();
        Route::post('/vinculos/{vinculo}/solicitar-conclusao', 'solicitarConclusao')->whereNumber('vinculo')->block();
    });
});

Route::middleware([ResolveWebAccount::class.':pessoa', UseResolvedWebActor::class])->prefix('admin')->group(function (): void {
    Route::middleware('admin:ongs.validar')->controller(WebOngRegistrationController::class)->group(function (): void {
        Route::get('/validacoes-ong/dados', 'queue')->block();
        Route::patch('/validacoes-ong/{ong}/status', 'decide')->whereNumber('ong')->block();
    });
    Route::middleware('admin:usuarios.validar')->controller(AdminPessoaValidationController::class)->group(function (): void {
        Route::get('/validacoes-pessoa/dados', 'index')->block();
        Route::get('/validacoes-pessoa/{pessoa}', 'show')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/assumir', 'assumir')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/exigir-padrinho', 'exigirPadrinho')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/solicitar-correcao', 'solicitarCorrecao')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/aprovar', 'aprovar')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/reprovar', 'reprovar')->whereNumber('pessoa')->block();
        Route::post('/validacoes-pessoa/{pessoa}/reprovar-definitivamente', 'reprovarDefinitivamente')->whereNumber('pessoa')->block();
        Route::patch('/documentos/{documento}/status', 'validarDocumento')->whereNumber('documento')->block();
        Route::get('/antecedentes/renovacoes', [AntecedentesController::class, 'renovacoes'])->block();
    });
    Route::middleware('admin:organizadores.validar')->controller(AdminOrganizadorController::class)->group(function (): void {
        Route::get('/solicitacoes-organizador/dados', 'index')->block();
        Route::get('/solicitacoes-organizador/{solicitacao}', 'show')->whereNumber('solicitacao')->block();
        Route::post('/solicitacoes-organizador/{solicitacao}/iniciar-analise', 'iniciarAnalise')->whereNumber('solicitacao')->block();
        Route::post('/solicitacoes-organizador/{solicitacao}/aprovar', 'aprovar')->whereNumber('solicitacao')->block();
        Route::post('/solicitacoes-organizador/{solicitacao}/reprovar', 'reprovar')->whereNumber('solicitacao')->block();
    });
    Route::middleware('admin:padrinhos.validar_conclusao')->controller(AdminPadrinhoController::class)->group(function (): void {
        Route::get('/padrinhos/conclusoes/dados', 'conclusoes')->block();
        Route::post('/padrinhos/conclusoes/{pedido}/aprovar', 'aprovar')->whereNumber('pedido')->block();
        Route::post('/padrinhos/conclusoes/{pedido}/reprovar', 'reprovar')->whereNumber('pedido')->block();
    });
    Route::middleware('admin:xp.campanhas.gerenciar')->controller(CampanhaXpController::class)->group(function (): void {
        Route::get('/campanhas-xp/dados', 'index')->block();
        Route::post('/campanhas-xp', 'store')->block();
        Route::patch('/campanhas-xp/{campanha}', 'update')->whereNumber('campanha')->block();
        Route::patch('/campanhas-xp/{campanha}/status', 'status')->whereNumber('campanha')->block();
    });
    Route::get('/solicitacoes-lgpd/dados', [SolicitacaoLgpdController::class, 'index'])->middleware('admin:lgpd.visualizar')->block();
    Route::patch('/solicitacoes-lgpd/{id}/status', [SolicitacaoLgpdController::class, 'updateStatus'])->whereNumber('id')->middleware('admin:lgpd.responder')->block();
    Route::get('/log-acoes/dados', [WebAccountDataController::class, 'audit'])->middleware('admin:admin.logs.visualizar')->block();
});
