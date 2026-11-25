<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Monitoria;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonitoriaService
{
    public function __construct(private EventoPolicy $policy) {}

    public static function regrasDecisao(): array
    {
        return [
            'confirmada' => ['required', 'boolean'],
            'justificativa' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function consultar(Inscricao $inscricao, Pessoa|Ong $ator): array
    {
        $titular = $ator instanceof Pessoa && $ator->pessoa_id === $inscricao->pessoa_id
            && AccountAccess::hasPersonalJourney($ator) && AccountAccess::canOperate($ator);

        if (! $titular && ! $this->policy->operate($ator, $inscricao->agenda->evento)) {
            throw new AuthorizationException('Você não pode consultar esta monitoria.');
        }

        return $this->situacao($inscricao->agenda, $inscricao, $inscricao->monitoria);
    }

    public function decidir(Inscricao $inscricao, Pessoa|Ong $ator, bool $confirmada, ?string $justificativa): array
    {
        return DB::transaction(function () use ($inscricao, $ator, $confirmada, $justificativa) {
            $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
            if (! $this->policy->operate($ator, $evento, true)) {
                throw new AuthorizationException('Somente o responsável operacional pode decidir esta monitoria.');
            }

            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $inscricao = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($evento->status_evento, ['ativo', 'finalizado'], true)
                || ! in_array($agenda->status_ativo, ['ativo', 'finalizado'], true)) {
                throw new ValidationConflictException('O estado do Evento ou da Agenda não permite decidir a monitoria.');
            }
            if (! $inscricao->declarou_estudante || $inscricao->status_inscricao !== 'participou'
                || ! $inscricao->presenca || $inscricao->presenca_confirmada_em === null) {
                throw new ValidationConflictException('A monitoria exige declaração de estudante e presença confirmada.');
            }
            if ((! $confirmada || $agenda->isFinalizado()) && trim((string) $justificativa) === '') {
                throw ValidationException::withMessages([
                    'justificativa' => 'Informe o motivo da rejeição ou da regularização após o encerramento.',
                ]);
            }

            $monitoria = $inscricao->monitoria()->lockForUpdate()->first();
            if ($monitoria?->confirmada !== null) {
                throw new ValidationConflictException('Já existe uma decisão registrada para esta monitoria.');
            }

            $monitoria ??= new Monitoria(['inscricao_id' => $inscricao->getKey()]);
            $monitoria->forceFill([
                'confirmada' => $confirmada,
                'validado_em' => now(),
                'validado_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'validado_por_id' => $ator->getKey(),
                'observacao' => $justificativa,
            ])->save();

            return $this->situacao($agenda, $inscricao, $monitoria);
        }, 3);
    }

    /** O chamador mantém o lock do Evento durante a emissão do certificado. */
    public function exigirValidacaoParaCertificado(Evento $evento, Pessoa $pessoa): void
    {
        $pendente = Inscricao::where('pessoa_id', $pessoa->pessoa_id)
            ->where('declarou_estudante', true)
            ->where('status_inscricao', 'participou')
            ->where('presenca', true)
            ->whereNotNull('presenca_confirmada_em')
            ->whereHas('agenda', fn (Builder $agenda) => $agenda->where('evento_id', $evento->evento_id)
                ->where('status_ativo', 'finalizado'))
            ->whereDoesntHave('monitoria', fn (Builder $monitoria) => $monitoria->where('confirmada', true)
                ->whereNotNull('validado_em')
                ->whereIn('validado_por_tipo', ['pessoa', 'ong'])
                ->whereNotNull('validado_por_id'))
            ->exists();

        if ($pendente) {
            throw new ValidationConflictException('A monitoria estudantil precisa ser validada em todas as Agendas correspondentes.');
        }
    }

    private function situacao(Agenda $agenda, Inscricao $inscricao, ?Monitoria $monitoria): array
    {
        $aplicavel = $inscricao->declarou_estudante && $agenda->status_ativo !== 'cancelado'
            && ! in_array($inscricao->status_inscricao, ['reprovado', 'desistente', 'não participou'], true);

        return [
            'inscricao_id' => $inscricao->inscricao_id,
            'declarou_estudante' => (bool) $inscricao->declarou_estudante,
            'status' => match (true) {
                ! $aplicavel => 'nao_aplicavel',
                $monitoria?->confirmada === true => 'validada',
                $monitoria?->confirmada === false => 'rejeitada',
                default => 'pendente',
            },
            'validado_em' => $monitoria?->validado_em?->toISOString(),
        ];
    }
}
