<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Support\Str;

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

    public function notificarEvento(Evento $evento, string $tipo, string $mensagem, bool $desistenciaSemPenalidade = false): void
    {
        $mensagem = $this->mensagem($mensagem, $desistenciaSemPenalidade);
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

    public function notificarAgenda(Agenda $agenda, string $tipo, string $mensagem, bool $desistenciaSemPenalidade = false): void
    {
        $mensagem = $this->mensagem($mensagem, $desistenciaSemPenalidade);
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

    public function registrarCancelamento(Agenda|Evento $recurso, Pessoa|Ong $ator, string $statusAnterior, bool $iniciado): void
    {
        AdminLog::create([
            'pessoa_id' => $ator instanceof Pessoa ? $ator->getKey() : null,
            'acao' => $recurso instanceof Agenda ? 'agenda.cancelada' : 'evento.cancelado',
            'recurso_type' => $recurso->getMorphClass(),
            'recurso_id' => $recurso->getKey(),
            'detalhes' => [
                'de' => $statusAnterior,
                'para' => 'cancelado',
                'ator_tipo' => $ator->getMorphClass(),
                'ator_id' => $ator->getKey(),
                'inicio_alcancado' => $iniciado,
            ],
        ]);
    }

    private function mensagem(string $mensagem, bool $desistenciaSemPenalidade): string
    {
        $aviso = $desistenciaSemPenalidade ? ' Você pode desistir sem penalização até o início da agenda.' : '';

        return Str::limit($mensagem, 255 - mb_strlen($aviso), '…').$aviso;
    }
}
