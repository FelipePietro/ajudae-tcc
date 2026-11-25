<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class AgendaManagementService
{
    public function __construct(private EventoOperationalService $operations) {}

    public static function regras(bool $criacao, string $prefixo = ''): array
    {
        $presenca = $criacao ? 'required' : 'sometimes';

        return [
            $prefixo.'data_inicio' => [$presenca, 'date'],
            $prefixo.'data_fim' => $criacao ? ['required', 'date', 'after:'.$prefixo.'data_inicio'] : ['sometimes', 'date'],
            $prefixo.'vagas_agenda' => [$presenca, 'integer', 'min:1', 'max:4294967295'],
        ];
    }

    public static function validarDuracao(Carbon|string $inicio, Carbon|string $fim, string $campo = 'data_fim'): void
    {
        if (Carbon::parse($inicio)->diffInMinutes(Carbon::parse($fim), false) < 30) {
            throw ValidationException::withMessages([$campo => 'A agenda deve durar ao menos 30 minutos.']);
        }
    }

    public static function atributos(array $dados): array
    {
        $atributos = Arr::only($dados, ['data_inicio', 'data_fim', 'vagas_agenda']);
        foreach (['data_inicio', 'data_fim'] as $campo) {
            if (isset($atributos[$campo])) {
                $atributos[$campo] = Carbon::parse($atributos[$campo])->setTimezone(config('app.timezone'));
            }
        }

        return $atributos;
    }

    public function criar(Pessoa|Ong $ator, Evento $evento, array $dados): Agenda
    {
        self::validarDuracao($dados['data_inicio'], $dados['data_fim']);

        return DB::transaction(function () use ($ator, $evento, $dados) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('createAgenda', [$evento, true]);
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode receber nova agenda.');
            }

            $agenda = $evento->agendas()->create(self::atributos($dados));
            $this->notificarEstruturaAlterada($evento);

            return $agenda->refresh();
        }, 3);
    }

    public function atualizar(Pessoa|Ong $ator, Agenda $agenda, array $dados): Agenda
    {
        return DB::transaction(function () use ($ator, $agenda, $dados) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('manage', [$evento, true]);
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || in_array($agenda->status_ativo, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Agenda terminal não pode ser alterada.');
            }

            self::validarDuracao($dados['data_inicio'] ?? $agenda->data_inicio, $dados['data_fim'] ?? $agenda->data_fim);
            $agenda->fill(self::atributos($dados));
            $alterados = array_keys($agenda->getDirty());
            if ($alterados === []) {
                return $agenda;
            }
            if (isset($dados['vagas_agenda']) && $dados['vagas_agenda'] < $this->operations->vagasOcupadas($agenda)) {
                throw new ValidationConflictException('As vagas não podem ser menores que as já ocupadas.');
            }

            $dataAlterada = array_intersect($alterados, ['data_inicio', 'data_fim']) !== [];
            $agenda->save();
            if ($dataAlterada) {
                $this->operations->concederDesistenciaSemPenalidade($agenda);
            }
            $this->operations->exigirReaprovacao($evento);
            $this->operations->notificarAgenda($agenda, 'agenda.alterada', 'A agenda do evento foi alterada.', $dataAlterada);

            return $agenda;
        }, 3);
    }

    public function excluir(Pessoa|Ong $ator, Agenda $agenda): void
    {
        DB::transaction(function () use ($ator, $agenda) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('deleteAgenda', [$evento, true]);
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || in_array($agenda->status_ativo, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Agenda terminal não pode ser excluída.');
            }
            if ($agenda->inscricoes()->exists()) {
                throw new ValidationConflictException('A agenda possui inscrições e deve ser cancelada para preservar o histórico.');
            }
            if (! $evento->agendas()->whereKeyNot($agenda->getKey())->exists()) {
                throw new ValidationConflictException('O Evento precisa manter pelo menos uma Agenda. Cancele a Agenda para preservar o histórico.');
            }

            $agenda->delete();
            $this->notificarEstruturaAlterada($evento);
        }, 3);
    }

    public function cancelar(Pessoa|Ong $ator, Agenda $agenda, string $motivo): Agenda
    {
        return DB::transaction(function () use ($ator, $agenda, $motivo) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('manage', [$evento, true]);
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true) || $agenda->status_ativo !== 'ativo') {
                throw new ValidationConflictException('A agenda não está ativa.');
            }

            $statusAnterior = $agenda->status_ativo;
            $canceladoEm = now();
            $iniciada = $agenda->data_inicio->lte($canceladoEm);
            $agenda->forceFill([
                'status_ativo' => 'cancelado',
                'motivo_cancelamento' => trim($motivo),
                'cancelado_em' => $canceladoEm,
                'cancelado_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'cancelado_por_id' => $ator->getKey(),
            ])->save();
            $this->operations->registrarCancelamento($agenda, $ator, $statusAnterior, $iniciada);
            $this->operations->notificarAgenda($agenda, 'agenda.cancelada', "Uma agenda do evento {$evento->nm_evento} foi cancelada. Sua participação não será penalizada.");

            return $agenda;
        }, 3);
    }

    private function notificarEstruturaAlterada(Evento $evento): void
    {
        $this->operations->exigirReaprovacao($evento);
        $this->operations->notificarEvento($evento, 'evento.alterado', "A estrutura de agendas do evento {$evento->nm_evento} foi alterada.");
    }
}
