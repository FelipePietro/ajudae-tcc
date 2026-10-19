<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;

class EventoOperationalService
{
    public const STATUS_OCUPANDO_VAGA = ['aprovado', 'participando', 'participou'];

    public function vagasOcupadas(Agenda $agenda): int
    {
        return $agenda->inscricoes()->whereIn('status_inscricao', self::STATUS_OCUPANDO_VAGA)->count();
    }

    public function exigirReaprovacao(Evento $evento): void
    {
        if (in_array($evento->status_evento, ['ativo', 'reprovado'], true)) {
            $evento->forceFill(['status_evento' => 'aguardando a confirmação'])->save();
        }
    }

    public function concederDesistenciaSemPenalidade(Agenda $agenda): void
    {
        $agenda->inscricoes()->whereNotIn('status_inscricao', ['reprovado', 'desistente'])
            ->update(['desistencia_sem_penalidade_ate' => $agenda->data_inicio]);
    }

    public function notificarEvento(Evento $evento, string $tipo, string $mensagem): void
    {
        $ids = Inscricao::whereHas('agenda', fn ($query) => $query->where('evento_id', $evento->evento_id))
            ->whereNotIn('status_inscricao', ['reprovado', 'desistente'])
            ->distinct()->pluck('pessoa_id');

        Pessoa::whereIn('pessoa_id', $ids)->get()->each(function (Pessoa $pessoa) use ($evento, $tipo, $mensagem) {
            $pessoa->notificacoes()->create([
                'tipo_notificacao' => $tipo,
                'mensagem_notificacao' => $mensagem,
                'evento_id' => $evento->evento_id,
            ]);
        });
    }

    public function notificarAgenda(Agenda $agenda, string $tipo, string $mensagem): void
    {
        $ids = $agenda->inscricoes()->whereNotIn('status_inscricao', ['reprovado', 'desistente'])
            ->distinct()->pluck('pessoa_id');

        Pessoa::whereIn('pessoa_id', $ids)->get()->each(function (Pessoa $pessoa) use ($agenda, $tipo, $mensagem) {
            $pessoa->notificacoes()->create([
                'tipo_notificacao' => $tipo,
                'mensagem_notificacao' => $mensagem,
                'agenda_id' => $agenda->agenda_id,
                'evento_id' => $agenda->evento_id,
            ]);
        });
    }
}
