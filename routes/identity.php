<?php

use App\Http\Controllers\PublicEventPagesController;
use App\Http\Controllers\SessionAccountController;
use App\Http\Controllers\SessionPagesController;
use App\Http\Controllers\WebApplicationController;
use App\Http\Controllers\WebCertificateController;
use App\Http\Controllers\WebEventModerationController;
use App\Http\Controllers\WebNotificationController;
use App\Http\Controllers\WebOrganizerLinksController;
use App\Http\Controllers\WebParticipationController;
use App\Http\Controllers\WebPessoaRegistrationController;
use App\Http\Controllers\WebPrivacyRequestController;
use App\Http\Controllers\WebRankingController;
use App\Http\Middleware\ResolveWebAccount;
use App\Support\WebNavigation;
use Illuminate\Support\Facades\Route;

Route::middleware(ResolveWebAccount::class.':public')->group(function (): void {
    require __DIR__.'/public-pages.php';

    Route::view('/ranking', 'ranking.index')->name('ranking')->block();
    Route::get('/ranking/dados', [WebRankingController::class, 'index'])->name('identity.ranking.index')->block();
    Route::view('/certificados/verificar', 'certificados.verificacao')->name('certificados.verificar')->block();
    Route::get('/certificados/verificar/dados', [WebCertificateController::class, 'verify'])
        ->middleware('throttle:30,1')->name('identity.certificates.verify')->block();
    Route::get('/pessoas/{id}/perfil', [SessionPagesController::class, 'publicPerson'])->whereNumber('id')->name('perfil.publico')->block();
    Route::get('/ongs/{id}/perfil', [SessionPagesController::class, 'publicOrganization'])->whereNumber('id')->name('ong.perfil.publico')->block();

    Route::get('/feed', [PublicEventPagesController::class, 'index'])->name('feed')->block();
    Route::get('/eventos/{id}', [PublicEventPagesController::class, 'show'])->whereNumber('id')->name('eventos.show')->block();
    Route::get('/cadastro', [WebPessoaRegistrationController::class, 'create'])->name('cadastro.pessoa')->block();
    Route::post('/cadastro', [WebPessoaRegistrationController::class, 'store'])
        ->middleware('throttle:5,1')->name('identity.registration.store')->block();
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
    Route::get('/conta/notificacoes/dados', [WebNotificationController::class, 'index'])->name('identity.notifications.index')->block();
    Route::patch('/conta/notificacoes/lidas', [WebNotificationController::class, 'markAllAsRead'])->name('identity.notifications.read-all')->block();
    Route::patch('/conta/notificacoes/{id}/lida', [WebNotificationController::class, 'markAsRead'])
        ->whereNumber('id')->name('identity.notifications.read')->block();
    Route::get('/conta/privacidade/solicitacoes', [WebPrivacyRequestController::class, 'index'])->name('identity.privacy.index')->block();
    Route::post('/conta/privacidade/solicitacoes', [WebPrivacyRequestController::class, 'store'])->name('identity.privacy.store')->block();
    Route::get('/conta/vinculos-organizador/dados', [WebOrganizerLinksController::class, 'index'])
        ->name('identity.organizer-links.index')->block();
    Route::post('/conta/vinculos-organizador/convites', [WebOrganizerLinksController::class, 'invite'])
        ->middleware('throttle:5,1')->name('identity.organizer-links.invite')->block();
    Route::post('/conta/vinculos-organizador/{vinculo}/resposta', [WebOrganizerLinksController::class, 'respond'])
        ->whereNumber('vinculo')->name('identity.organizer-links.respond')->block();
    Route::delete('/conta/vinculos-organizador/{vinculo}', [WebOrganizerLinksController::class, 'end'])
        ->whereNumber('vinculo')->name('identity.organizer-links.end')->block();
    Route::get('/admin/fila-eventos/dados', [WebEventModerationController::class, 'index'])
        ->name('identity.admin.events.index')->block();
    Route::patch('/admin/fila-eventos/{evento}/decisao', [WebEventModerationController::class, 'decide'])
        ->whereNumber('evento')->name('identity.admin.events.decide')->block();
});

Route::middleware(ResolveWebAccount::class.':jornada-pessoa')->group(function (): void {
    Route::get('/perfil', [SessionPagesController::class, 'person'])->name('perfil')->block();
    Route::get('/perfil/privacidade', [SessionPagesController::class, 'privatePrivacy'])->name('perfil.privacidade')->block();
    Route::get('/candidatura', [PublicEventPagesController::class, 'application'])->name('candidatura.form')->block();
    Route::get('/candidatura/{evento}/agendas', [WebApplicationController::class, 'agendas'])
        ->whereNumber('evento')->name('identity.application.agendas')->block();
    Route::post('/candidatura/{evento}/enviar', [WebApplicationController::class, 'store'])
        ->whereNumber('evento')->name('identity.application.store')->block();
    Route::get('/minhas-candidaturas', [SessionPagesController::class, 'applications'])->name('candidaturas')->block();
    Route::get('/minhas-candidaturas/dados', [WebApplicationController::class, 'index'])->name('identity.application.index')->block();
    Route::delete('/minhas-candidaturas/{inscricao}', [WebApplicationController::class, 'withdraw'])
        ->whereNumber('inscricao')->name('identity.application.withdraw')->block();
    Route::view('/minhas-participacoes', 'participacoes.pessoal')->name('participacoes')->block();
    Route::get('/minhas-participacoes/dados', [WebParticipationController::class, 'index'])->name('identity.participation.index')->block();
    Route::post('/minhas-participacoes/{evento}/avaliacao-ong', [WebParticipationController::class, 'rate'])
        ->whereNumber('evento')->name('identity.participation.rate')->block();
    Route::view('/meus-certificados', 'certificados.lista')->name('certificados.index')->block();
    Route::get('/meus-certificados/dados', [WebCertificateController::class, 'index'])->name('identity.certificates.index')->block();
    Route::post('/meus-certificados/eventos/{evento}/emitir', [WebCertificateController::class, 'issue'])
        ->whereNumber('evento')->name('identity.certificates.issue')->block();
    Route::get('/meus-certificados/{certificado}/pdf', [WebCertificateController::class, 'download'])
        ->whereNumber('certificado')->name('identity.certificates.download')->block();
    Route::get('/perfil/editar', [SessionPagesController::class, 'editPerson'])->name('perfil.editar')->block();
    Route::get('/perfil/preferencias', [SessionPagesController::class, 'preferences'])->name('perfil.preferencias')->block();
});

Route::middleware(ResolveWebAccount::class.':pessoa')->group(function (): void {
    Route::get('/notificacoes', [SessionPagesController::class, 'personNotifications'])->name('notificacoes')->block();
    Route::get('/organizador', [SessionPagesController::class, 'organizer'])->name('organizador.workspace')->block();
    Route::get('/organizador/eventos/criar', [SessionPagesController::class, 'organizerCreate'])->name('organizador.eventos.criar')->block();
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
    Route::get('/ong/perfil/editar', [SessionPagesController::class, 'editOrganization'])->name('ong.perfil.editar')->block();
    Route::get('/ong/painel', [SessionPagesController::class, 'institutionalPanel'])->name('ong.painel')->block();
    Route::get('/ong/privacidade', [SessionPagesController::class, 'privatePrivacy'])->name('ong.privacidade')->block();
    foreach (SessionPagesController::INSTITUTIONAL_AREAS as $name => [$uri]) {
        Route::get($uri, [SessionPagesController::class, 'institutionalArea'])->name($name)->block();
    }
});
