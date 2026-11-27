<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Support\Facades\DB;

final class CandidaturaService
{
    public const LIMITE_CARTA = 2000;

    public const INSCRICAO_CANCELAVEL = ['pendente', 'aprovado', 'participando'];

    public function __construct(private readonly EventoOperationalService $operations) {}

    public static function regras(): array
    {
        return [
            'declarou_estudante' => ['sometimes', 'boolean'],
            'carta_apresentacao' => ['nullable', 'string', 'max:'.self::LIMITE_CARTA],
        ];
    }

    public function inscrever(Pessoa $pessoa, int $agendaId, array $dados, ?int $eventoId = null): Inscricao
    {
        $agenda = Agenda::query()->whereKey($agendaId)
            ->when($eventoId !== null, fn ($query) => $query->where('evento_id', $eventoId))
            ->firstOrFail();

        return DB::transaction(function () use ($agenda, $pessoa, $dados) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            $candidate = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($candidate->isApprovedVolunteer() && AccountAccess::canOperate($candidate)
                && (int) $candidate->auth_version === (int) $pessoa->auth_version, 403);

            $motivo = $this->impedimento(
                $evento,
                $agenda,
                $agenda->inscricoes()->where('pessoa_id', $candidate->getKey())->exists(),
                $this->operations->vagasOcupadas($agenda),
            );
            if ($motivo !== null) {
                throw new ValidationConflictException($motivo);
            }

            $inscricao = Inscricao::create([
                'agenda_id' => $agenda->getKey(),
                'pessoa_id' => $candidate->getKey(),
                'status_inscricao' => 'pendente',
                'dt_inscricao' => now(),
                'declarou_estudante' => $dados['declarou_estudante'] ?? false,
                'carta_apresentacao' => $dados['carta_apresentacao'] ?? null,
            ]);

            $responsavel = $evento->responsavelPessoa ?? $evento->ong ?? $evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.criada',
                'mensagem_notificacao' => "Nova inscrição no evento {$evento->nm_evento}.",
                'agenda_id' => $agenda->getKey(),
                'evento_id' => $evento->getKey(),
            ]);

            return $inscricao;
        });
    }

    public function agendas(Evento $evento, Pessoa $pessoa): array
    {
        return $evento->agendas()
            ->withCount(['inscricoes as ocupadas' => fn ($query) => $query
                ->whereIn('status_inscricao', EventoOperationalService::STATUS_OCUPANDO_VAGA)])
            ->withExists(['inscricoes as inscrita' => fn ($query) => $query
                ->where('pessoa_id', $pessoa->getKey())])
            ->orderBy('data_inicio')
            ->orderBy('agenda_id')
            ->get()
            ->map(fn (Agenda $agenda) => [
                'id' => $agenda->getKey(),
                'inicio' => $agenda->data_inicio->toISOString(),
                'fim' => $agenda->data_fim->toISOString(),
                'vagas' => $agenda->vagas_agenda,
                'disponiveis' => max(0, $agenda->vagas_agenda - $agenda->ocupadas),
                'impedimento' => $this->impedimento($evento, $agenda, $agenda->inscrita, $agenda->ocupadas),
            ])->all();
    }

    public function desistir(Pessoa $pessoa, Inscricao $inscricao): Inscricao
    {
        abort_unless(AccountAccess::hasPersonalJourney($pessoa) && $pessoa->getKey() === $inscricao->pessoa_id, 403);
        $eventoId = $inscricao->agenda->evento_id;

        return DB::transaction(function () use ($pessoa, $inscricao, $eventoId) {
            $evento = Evento::whereKey($eventoId)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            $titular = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(AccountAccess::hasPersonalJourney($titular) && AccountAccess::canOperate($titular)
                && (int) $titular->auth_version === (int) $pessoa->auth_version && $titular->getKey() === $locked->pessoa_id, 403);

            if (! $this->podeDesistir($evento, $agenda, $locked)) {
                throw new ValidationConflictException('Esta inscrição não pode mais ser cancelada.');
            }

            $agora = now();
            $semPenalidade = $locked->desistencia_sem_penalidade_ate !== null
                && $agora->lessThanOrEqualTo($locked->desistencia_sem_penalidade_ate);
            $locked->update([
                'status_inscricao' => 'desistente',
                'desistiu_em' => $agora,
                'desistencia_tardia' => $agora->diffInSeconds($agenda->data_inicio, false) < 24 * 3600,
                'desistencia_sem_penalidade' => $semPenalidade
                    || $agenda->status_ativo === 'cancelado' || $evento->status_evento === 'cancelado',
            ]);

            $responsavel = $evento->responsavelPessoa ?? $evento->ong ?? $evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.desistente',
                'mensagem_notificacao' => "Uma pessoa desistiu da agenda do evento {$evento->nm_evento}.",
                'agenda_id' => $agenda->getKey(),
                'evento_id' => $evento->getKey(),
            ]);

            return $locked;
        });
    }

    public function podeDesistir(Evento $evento, Agenda $agenda, Inscricao $inscricao): bool
    {
        return now()->lessThan($agenda->data_inicio)
            && in_array($inscricao->status_inscricao, self::INSCRICAO_CANCELAVEL, true)
            && $inscricao->presenca_confirmada_em === null
            && $agenda->status_ativo !== 'finalizado'
            && $evento->status_evento !== 'finalizado';
    }

    private function impedimento(Evento $evento, Agenda $agenda, bool $inscrita, int $ocupadas): ?string
    {
        return match (true) {
            $evento->status_evento !== 'ativo' || ! $agenda->isAtivo() => 'O evento ou a agenda não está disponível para candidaturas.',
            now()->greaterThanOrEqualTo($agenda->data_inicio->copy()->subHour()) => 'A candidatura fecha uma hora antes do início da agenda.',
            $inscrita => 'Você já possui uma inscrição nesta agenda.',
            $ocupadas >= $agenda->vagas_agenda => 'A agenda já está lotada.',
            default => null,
        };
    }
}
