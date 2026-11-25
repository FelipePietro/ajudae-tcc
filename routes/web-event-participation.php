<?php

use App\Http\Controllers\WebEventParticipationController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestao')->name('identity.management.')->group(function (): void {
    Route::get('/agendas/{agenda}/inscricoes', [WebEventParticipationController::class, 'candidates'])
        ->whereNumber('agenda')->name('candidates.index')->block();
    Route::patch('/inscricoes/{inscricao}/selecao', [WebEventParticipationController::class, 'select'])
        ->whereNumber('inscricao')->name('candidates.select')->block();
    Route::patch('/inscricoes/{inscricao}/presenca', [WebEventParticipationController::class, 'attendance'])
        ->whereNumber('inscricao')->name('participation.attendance')->block();
    Route::get('/inscricoes/{inscricao}/monitoria', [WebEventParticipationController::class, 'monitoring'])
        ->whereNumber('inscricao')->name('participation.monitoring')->block();
    Route::patch('/inscricoes/{inscricao}/monitoria', [WebEventParticipationController::class, 'decideMonitoring'])
        ->whereNumber('inscricao')->name('participation.monitoring.decide')->block();
    Route::get('/inscricoes/{inscricao}/justificativa', [WebEventParticipationController::class, 'justification'])
        ->whereNumber('inscricao')->name('participation.justification')->block();
    Route::post('/inscricoes/{inscricao}/justificativa', [WebEventParticipationController::class, 'submitJustification'])
        ->whereNumber('inscricao')->name('participation.justification.submit')->block();
    Route::patch('/inscricoes/{inscricao}/justificativa', [WebEventParticipationController::class, 'decideJustification'])
        ->whereNumber('inscricao')->name('participation.justification.decide')->block();
    Route::get('/eventos/{evento}/avaliacoes', [WebEventParticipationController::class, 'volunteerRatings'])
        ->whereNumber('evento')->name('participation.volunteer-ratings')->block();
    Route::post('/eventos/{evento}/avaliacoes/{pessoa}', [WebEventParticipationController::class, 'volunteerRating'])
        ->whereNumber(['evento', 'pessoa'])->name('participation.volunteer-rating')->block();
    Route::patch('/agendas/{agenda}/finalizar', [WebEventParticipationController::class, 'finishAgenda'])
        ->whereNumber('agenda')->name('participation.finish-agenda')->block();
    Route::post('/eventos/{evento}/finalizar', [WebEventParticipationController::class, 'finishEvent'])
        ->whereNumber('evento')->name('participation.finish-event')->block();
});
