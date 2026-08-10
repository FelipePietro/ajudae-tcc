<?php

namespace App\Console\Commands;

use App\Models\Pessoa;
use App\Models\Ong;
use App\Models\Evento;
use Illuminate\Console\Command;

class ExcluirContasPendentes extends Command
{
    protected $signature = 'app:excluir-contas-pendentes';

    protected $description = 'Exclui definitivamente contas com exclusão pendente';

    public function handle(): int
    {
        Pessoa::where('exclusao_pendente', true)
            ->where('deletar_em', '<=', now())
            ->each(function ($pessoa) {

                $possuiEventosAtivos = Evento::where(
                    'pessoa_id',
                    $pessoa->pessoa_id
                )
                    ->whereNotIn('status_evento', [
                        'finalizado',
                        'cancelado',
                        'reprovado'
                    ])
                    ->exists();

                if ($possuiEventosAtivos) {
                    $this->warn(
                        "Pessoa {$pessoa->pessoa_id} não excluída: possui eventos ativos."
                    );

                    return;
                }

                Evento::where('pessoa_id', $pessoa->pessoa_id)
                    ->whereIn('status_evento', [
                        'finalizado',
                        'cancelado',
                        'reprovado'
                    ])
                    ->update([
                        'pessoa_id' => null
                    ]);

                $pessoa->tokens()->delete();
                $pessoa->delete();

                $this->info(
                    "Pessoa {$pessoa->pessoa_id} excluída."
                );
            });


        Ong::where('exclusao_pendente', true)
            ->where('deletar_em', '<=', now())
            ->each(function ($ong) {

                $possuiEventosAtivos = Evento::where(
                    'ong_id',
                    $ong->ong_id
                )
                    ->whereNotIn('status_evento', [
                        'finalizado',
                        'cancelado',
                        'reprovado'
                    ])
                    ->exists();

                if ($possuiEventosAtivos) {
                    $this->warn(
                        "ONG {$ong->ong_id} não excluída: possui eventos ativos."
                    );

                    return;
                }

                $ong->tokens()->delete();
                $ong->delete();

                $this->info(
                    "ONG {$ong->ong_id} excluída."
                );
            });

        return self::SUCCESS;
    }
}