<?php

use App\Http\Controllers\PublicEventPagesController;
use App\Http\Controllers\SessionAccountController;
use App\Http\Controllers\SessionPagesController;
use App\Http\Middleware\ResolveWebAccount;
use App\Support\WebNavigation;
use Illuminate\Support\Facades\Route;

Route::middleware(ResolveWebAccount::class.':public')->group(function (): void {
    require __DIR__.'/public-pages.php';

    Route::view('/ranking', 'auth.feature-unavailable', [
        'title' => 'Ranking', 'message' => 'A classificação ainda está indisponível.',
        'backRoute' => 'feed', 'backLabel' => 'Voltar ao feed',
    ])->name('ranking')->block();

    Route::get('/feed', [PublicEventPagesController::class, 'index'])->name('feed')->block();
    Route::get('/eventos/{id}', [PublicEventPagesController::class, 'show'])->whereNumber('id')->name('eventos.show')->block();
    Route::view('/cadastro', 'auth.registration-unavailable', ['institution' => false])->name('cadastro.pessoa')->block();
    Route::view('/cadastro/ong', 'auth.registration-unavailable', ['institution' => true])->name('ong.cadastrar.form')->block();
});

Route::middleware(ResolveWebAccount::class)->group(function (): void {
    Route::get('/login', [SessionAccountController::class, 'loginForm'])->name('login')->block();
    Route::post('/login', [SessionAccountController::class, 'login'])->name('login.store')->block();
    Route::post('/logout', [SessionAccountController::class, 'logout'])->name('logout')->block();
});

Route::middleware(ResolveWebAccount::class.':required')->group(function (): void {
    Route::get('/conta', [SessionAccountController::class, 'account'])->name('conta')->block();
    Route::post('/conta/senha', [SessionAccountController::class, 'password'])->name('identity.password')->block();
    Route::post('/conta/exclusao', [SessionAccountController::class, 'scheduleDeletion'])->name('identity.deletion.schedule')->block();
    Route::patch('/conta/exclusao', [SessionAccountController::class, 'cancelDeletion'])->name('identity.deletion.cancel')->block();
});

Route::middleware(ResolveWebAccount::class.':pessoa')->group(function (): void {
    Route::get('/perfil', [SessionPagesController::class, 'person'])->name('perfil')->block();
    Route::get('/perfil/privacidade', [SessionPagesController::class, 'privatePrivacy'])->name('perfil.privacidade')->block();
    Route::get('/minhas-candidaturas', [SessionPagesController::class, 'applications'])->name('candidaturas')->block();
    Route::view('/minhas-participacoes', 'auth.feature-unavailable', [
        'title' => 'Minhas participações', 'message' => 'O histórico de participações ainda está indisponível.',
        'backRoute' => 'perfil', 'backLabel' => 'Voltar ao perfil',
    ])->name('participacoes')->block();
    Route::get('/notificacoes', [SessionPagesController::class, 'personNotifications'])->name('notificacoes')->block();
    Route::get('/organizador', [SessionPagesController::class, 'organizer'])->name('organizador.workspace')->block();
    Route::get('/organizador/eventos', [SessionPagesController::class, 'organizerEvents'])->name('organizador.eventos')->block();
    Route::get('/admin', [SessionPagesController::class, 'admin'])->name('admin.admin')->block();
    Route::get('/admin/solicitacoes-lgpd', [SessionPagesController::class, 'privacyQueue'])->name('admin.solicitacoes-lgpd')->block();
    foreach (array_diff(array_keys(WebNavigation::ADMIN_AREAS), ['solicitacoes-lgpd']) as $area) {
        Route::get('/admin/'.$area, [SessionPagesController::class, 'adminArea'])
            ->defaults('area', $area)->name('admin.'.$area)->block();
    }
});

Route::middleware(ResolveWebAccount::class.':ong')->group(function (): void {
    Route::get('/ong/perfil', [SessionPagesController::class, 'organization'])->name('ong.perfil')->block();
    Route::get('/ong/painel', [SessionPagesController::class, 'institutionalPanel'])->name('ong.painel')->block();
    Route::get('/ong/privacidade', [SessionPagesController::class, 'privatePrivacy'])->name('ong.privacidade')->block();
    foreach (SessionPagesController::INSTITUTIONAL_AREAS as $name => [$uri]) {
        Route::get($uri, [SessionPagesController::class, 'institutionalArea'])->name($name)->block();
    }
});
