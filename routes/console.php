<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// A exclusão efetiva está suspensa; o scheduler só produz contagens.
Schedule::command('app:excluir-contas-pendentes --dry-run')
    ->hourly();

// Em homologação, somente relatório. Ativar exclusão após política, backup e revisão humana.
Schedule::command('documentos:excluir-retidos --dry-run --limit=100')
    ->daily();

Schedule::command('antecedentes:alertar-renovacao')
    ->daily();

// Reconciliação remove objetos órfãos; execução automática aguarda revisão operacional.
