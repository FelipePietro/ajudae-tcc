<?php

namespace App\Console\Commands;

use App\Models\Pessoa;
use App\Services\AntecedentesService;
use Illuminate\Console\Command;

class AlertarRenovacaoAntecedentes extends Command
{
    protected $signature = 'antecedentes:alertar-renovacao';

    protected $description = 'Avisa uma vez por versão aprovada quando os antecedentes vencem';

    public function handle(AntecedentesService $service): int
    {
        Pessoa::query()
            ->whereHas('documentos', fn ($query) => $query
                ->where('tipo', 'antecedentes_criminais')->where('status', 'aprovado')
                ->where('expires_at', '<=', now()))
            ->orderBy('pessoa_id')
            ->chunkById(100, fn ($pessoas) => $pessoas->each(fn (Pessoa $pessoa) => $service->alertarSeVencido($pessoa)), 'pessoa_id');

        return self::SUCCESS;
    }
}
