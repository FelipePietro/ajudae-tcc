<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\JustificativaParticipacao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class JustificativaParticipacaoService
{
    public function __construct(private EventoPolicy $policy) {}

    public static function regrasEnvio(): array
    {
        return ['motivo' => ['required', 'string', 'max:2000']];
    }

    public static function regrasDecisao(): array
    {
        return ['deferida' => ['required', 'boolean'], ...self::regrasEnvio()];
    }

    public function consultar(Inscricao $inscricao, Pessoa|Ong $ator): JustificativaParticipacao
    {
        if (! $this->eTitular($inscricao, $ator) && ! $this->policy->operate($ator, $inscricao->agenda->evento)) {
            throw new AuthorizationException('Você não pode consultar esta justificativa.');
        }

        return $inscricao->justificativaParticipacao()->firstOrFail();
    }

    public function submeter(Inscricao $inscricao, Pessoa|Ong $ator, string $motivo): JustificativaParticipacao
    {
        if (! $this->eTitular($inscricao, $ator)) {
            throw new AuthorizationException('Somente o titular pode enviar a justificativa.');
        }

        return DB::transaction(function () use ($inscricao, $ator, $motivo) {
            [$evento, $agenda, $inscricao] = $this->bloquearContexto($inscricao);
            $pessoa = Pessoa::whereKey($ator->getKey())->lockForUpdate()->firstOrFail();
            if (! $this->eTitular($inscricao, $pessoa) || (int) $pessoa->auth_version !== (int) $ator->auth_version) {
                throw new AuthorizationException('Somente o titular pode enviar a justificativa.');
            }
            $this->garantirOcorrenciaElegivel($evento, $agenda, $inscricao);

            if ($inscricao->justificativaParticipacao()->exists()) {
                throw new ValidationConflictException('Já existe uma justificativa para esta inscrição.');
            }

            return $inscricao->justificativaParticipacao()->create(['motivo' => $motivo])->refresh();
        }, 3);
    }

    public function decidir(Inscricao $inscricao, Pessoa|Ong $ator, bool $deferida, string $motivo): JustificativaParticipacao
    {
        return DB::transaction(function () use ($inscricao, $ator, $deferida, $motivo) {
            [$evento, $agenda, $inscricao] = $this->bloquearContexto($inscricao);
            if ($this->eTitular($inscricao, $ator) || ! $this->policy->operate($ator, $evento, true)) {
                throw new AuthorizationException('Somente o responsável operacional pode decidir esta justificativa.');
            }
            $this->garantirOcorrenciaElegivel($evento, $agenda, $inscricao);

            $justificativa = $inscricao->justificativaParticipacao()->lockForUpdate()->firstOrFail();
            if ($justificativa->status !== 'pendente') {
                throw new ValidationConflictException('Esta justificativa já foi decidida.');
            }

            $justificativa->forceFill([
                'status' => $deferida ? 'deferida' : 'indeferida',
                'motivo_decisao' => $motivo,
                'decidido_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'decidido_por_id' => $ator->getKey(),
                'decidido_em' => now(),
            ])->save();

            return $justificativa;
        }, 3);
    }

    private function eTitular(Inscricao $inscricao, Pessoa|Ong $ator): bool
    {
        return $ator instanceof Pessoa && $ator->pessoa_id === $inscricao->pessoa_id
            && AccountAccess::hasPersonalJourney($ator) && AccountAccess::canOperate($ator);
    }

    private function bloquearContexto(Inscricao $inscricao): array
    {
        $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
        $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
        $inscricao = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();

        return [$evento, $agenda, $inscricao];
    }

    private function garantirOcorrenciaElegivel(Evento $evento, Agenda $agenda, Inscricao $inscricao): void
    {
        if (! in_array($evento->status_evento, ['ativo', 'finalizado'], true) || $agenda->status_ativo === 'cancelado') {
            throw new ValidationConflictException('O estado do Evento ou da Agenda não permite alterar a justificativa.');
        }

        $elegivel = match ($inscricao->status_inscricao) {
            'não participou' => $inscricao->presenca === false && $inscricao->presenca_confirmada_em !== null,
            'desistente' => $inscricao->desistiu_em !== null && ! $inscricao->desistencia_sem_penalidade,
            default => false,
        };
        if (! $elegivel) {
            throw new ValidationConflictException('A inscrição não possui ausência ou desistência sujeita a justificativa.');
        }
    }
}
