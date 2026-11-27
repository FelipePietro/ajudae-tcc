<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\AdminLog;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class EventoParticipationService
{
    private const TRANSICOES_SELECAO = [
        'pendente' => ['aprovado', 'reprovado'],
        'aprovado' => ['participando'],
    ];

    public function __construct(
        private EventoOperationalService $operations,
        private MatchService $match,
        private ConfiabilidadeService $confiabilidade,
        private AvaliacaoService $avaliacoes,
        private XpService $xp,
    ) {}

    public static function regrasConsultaInscritos(): array
    {
        return [
            'status' => [
                'sometimes',
                Rule::in(['pendente', 'aprovado', 'reprovado', 'participando', 'participou', 'não participou']),
            ],
        ];
    }

    public static function regrasSelecao(): array
    {
        return ['status_inscricao' => ['required', Rule::in(['aprovado', 'reprovado', 'participando'])]];
    }

    public static function regrasPresenca(): array
    {
        return [
            'presenca' => ['required', 'boolean'],
            'carga_horaria' => ['prohibited'],
            'observacao' => ['prohibited'],
        ];
    }

    public function listarInscritos(Agenda $agenda, Pessoa|Ong $ator, ?string $status, int $porPagina): LengthAwarePaginator
    {
        Gate::forUser($ator)->authorize('manage', $agenda);
        $evento = $agenda->evento;
        $evento->loadMissing(['habilidades', 'causas', 'recursos']);
        $inscricoes = $agenda->inscricoes()
            ->with(['pessoa.habilidades', 'pessoa.causas', 'pessoa.recursos'])
            ->when($status !== null, fn ($query) => $query->where('status_inscricao', $status))
            ->orderBy('inscricao_id')
            ->paginate($porPagina);

        $ids = $inscricoes->getCollection()->pluck('pessoa_id');
        $resumos = $this->confiabilidade->resumosPublicosPorPessoas($ids);
        $medias = $this->avaliacoes->mediasPorPessoas($ids);
        $mostrarCarta = app(EventoPolicy::class)->operate($ator, $evento);

        foreach ($inscricoes as $inscricao) {
            $inscricao->pessoa->setAttribute('media_avaliacao_calculada', $medias[$inscricao->pessoa_id] ?? null);
            $inscricao->pessoa->setAttribute('confiabilidade_resumo', $resumos[$inscricao->pessoa_id]);
            $inscricao->setAttribute('match', $this->match->calcular($evento, $inscricao->pessoa));
            $inscricao->setAttribute('confiabilidade', $resumos[$inscricao->pessoa_id]);
            $inscricao->setAttribute('carta_visivel', $mostrarCarta);
        }

        return $inscricoes;
    }

    public function selecionar(Inscricao $inscricao, Pessoa|Ong $ator, string $status): Inscricao
    {
        Gate::forUser($ator)->authorize('operate', $inscricao->agenda->evento);
        if (! in_array($status, self::TRANSICOES_SELECAO[$inscricao->status_inscricao] ?? [], true)) {
            throw ValidationException::withMessages(['status_inscricao' => 'Transição de status inválida.']);
        }

        return DB::transaction(function () use ($inscricao, $ator, $status) {
            $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('operate', [$evento, true]);
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $inscricao = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo' || $evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O Evento e a Agenda precisam estar ativos para alterar a seleção.');
            }
            if (! in_array($status, self::TRANSICOES_SELECAO[$inscricao->status_inscricao] ?? [], true)) {
                throw new ValidationConflictException('Transição de inscrição inválida.');
            }
            if ($status === 'aprovado' && $this->operations->vagasOcupadas($agenda) >= $agenda->vagas_agenda) {
                throw new ValidationConflictException('Não há vagas disponíveis para aprovação.');
            }

            $statusAnterior = $inscricao->status_inscricao;
            $inscricao->update(['status_inscricao' => $status]);
            $this->registrarDecisao($inscricao, $ator, 'inscricao.selecao', $statusAnterior, $status);
            $inscricao->pessoa->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.status_atualizado',
                'mensagem_notificacao' => "Sua inscrição foi atualizada para {$status}.",
                'agenda_id' => $agenda->getKey(),
                'evento_id' => $evento->getKey(),
            ]);

            return $inscricao->unsetRelations();
        }, 3);
    }

    public function registrarPresenca(Inscricao $inscricao, Pessoa|Ong $ator, bool $presente): Inscricao
    {
        return DB::transaction(function () use ($inscricao, $ator, $presente) {
            $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('operate', [$evento, true]);
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $inscricao = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo' || $evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O Evento e a Agenda precisam estar ativos para registrar presença ou ausência.');
            }
            if (now()->lessThan($agenda->data_inicio)) {
                throw new ValidationConflictException('A presença só pode ser registrada após o início da agenda.');
            }
            if (! in_array($inscricao->status_inscricao, ['aprovado', 'participando'], true)
                || $inscricao->presenca_confirmada_em !== null) {
                throw new ValidationConflictException('A inscrição não está apta a receber presença ou ausência.');
            }

            $duracao = round($agenda->data_inicio->diffInSeconds($agenda->data_fim, false) / 3600, 2);
            $inscricao->forceFill([
                'presenca' => $presente,
                'presenca_confirmada_em' => now(),
                'presenca_confirmada_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'presenca_confirmada_por_id' => $ator->getKey(),
                'carga_horaria' => $presente ? $duracao : null,
                'status_inscricao' => $presente ? 'participou' : 'não participou',
            ])->save();

            return $inscricao;
        }, 3);
    }

    public function finalizarAgenda(Agenda $agenda, Pessoa|Ong $ator): Agenda
    {
        return DB::transaction(function () use ($agenda, $ator) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('operate', [$evento, true]);
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if ($evento->status_evento !== 'ativo' || $agenda->status_ativo !== 'ativo') {
                throw new ValidationConflictException('A agenda não pode ser finalizada neste estado.');
            }
            if (now()->lessThan($agenda->data_fim)) {
                throw new ValidationConflictException('A agenda só pode ser finalizada após seu término.');
            }
            $pendencias = $agenda->inscricoes()
                ->where(function ($query) {
                    $query->whereIn('status_inscricao', ['aprovado', 'participando'])
                        ->orWhere(function ($query) {
                            $query->where('status_inscricao', 'participou')
                                ->where(function ($query) {
                                    $query->where('presenca', '!=', true)
                                        ->orWhereNull('presenca')
                                        ->orWhereNull('presenca_confirmada_em');
                                });
                        })
                        ->orWhere(function ($query) {
                            $query->where('status_inscricao', 'não participou')
                                ->where(function ($query) {
                                    $query->where('presenca', '!=', false)
                                        ->orWhereNull('presenca')
                                        ->orWhereNull('presenca_confirmada_em');
                                });
                        });
                })
                ->count();
            if ($pendencias > 0) {
                $substantivo = $pendencias === 1 ? 'inscrição' : 'inscrições';
                throw new ValidationConflictException(
                    "A Agenda possui {$pendencias} {$substantivo} aguardando confirmação de presença ou ausência."
                );
            }

            $agenda->finalizar();
            $this->xp->concederAgenda($agenda);
            $this->registrarDecisao($agenda, $ator, 'agenda.finalizada', 'ativo', 'finalizado');

            return $agenda;
        }, 3);
    }

    public function consultarAvaliacoesVoluntarios(Evento $evento, Pessoa|Ong $ator, int $porPagina): array
    {
        Gate::forUser($ator)->authorize('operate', $evento);
        $participantes = $this->participantesConfirmados($evento)
            ->join('pessoa', 'pessoa.pessoa_id', '=', 'inscricao.pessoa_id')
            ->leftJoin('avaliacao_voluntario as avaliacao', fn (JoinClause $join) => $join
                ->on('avaliacao.pessoa_id', '=', 'pessoa.pessoa_id')
                ->where('avaliacao.evento_id', $evento->getKey())
                ->whereBetween('avaliacao.nota', [1, 5]))
            ->select(['inscricao.pessoa_id', 'pessoa.nm_pessoa', 'avaliacao.nota'])
            ->distinct()
            ->orderBy('inscricao.pessoa_id')
            ->simplePaginate($porPagina)
            ->withQueryString();

        return [
            'pode_avaliar' => $evento->status_evento === 'ativo' && $this->agendasPendentes($evento) === 0,
            'participantes' => $participantes,
        ];
    }

    public function finalizarEvento(int $eventoId, Pessoa|Ong $ator): Evento
    {
        return DB::transaction(function () use ($eventoId, $ator) {
            $evento = Evento::whereKey($eventoId)->lockForUpdate()->firstOrFail();
            Gate::forUser($ator)->authorize('manage', [$evento, true]);

            if ($evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O evento não está ativo para finalização.');
            }
            if (! $evento->agendas()->exists()) {
                throw new ValidationConflictException('O evento precisa ter ao menos uma Agenda para ser finalizado.');
            }

            $agendasPendentes = $this->agendasPendentes($evento);
            if ($agendasPendentes > 0) {
                throw new ValidationConflictException("O evento possui {$agendasPendentes} agendas pendentes.");
            }

            $participantes = $this->participantesConfirmados($evento)->distinct()->pluck('inscricao.pessoa_id');

            $avaliados = DB::table('avaliacao_voluntario')
                ->where('evento_id', $evento->evento_id)
                ->whereIn('pessoa_id', $participantes)
                ->whereBetween('nota', [1, 5])
                ->pluck('pessoa_id');
            $faltantes = $participantes->diff($avaliados)->count();
            if ($faltantes > 0) {
                $substantivo = $faltantes === 1 ? 'pessoa' : 'pessoas';
                throw new ValidationConflictException("O evento possui {$faltantes} {$substantivo} aguardando avaliação.");
            }

            $evento->forceFill([
                'status_evento' => 'finalizado',
                'finalizado_em' => now(),
                'finalizado_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'finalizado_por_id' => $ator->getKey(),
            ])->save();

            return $evento;
        }, 3);
    }

    private function agendasPendentes(Evento $evento): int
    {
        return $evento->agendas()->whereNotIn('status_ativo', ['finalizado', 'cancelado'])->count();
    }

    private function registrarDecisao(Agenda|Inscricao $recurso, Pessoa|Ong $ator, string $acao, string $anterior, string $atual): void
    {
        AdminLog::create([
            'pessoa_id' => $ator instanceof Pessoa ? $ator->getKey() : null,
            'acao' => $acao,
            'recurso_type' => $recurso->getMorphClass(),
            'recurso_id' => $recurso->getKey(),
            'detalhes' => [
                'de' => $anterior,
                'para' => $atual,
                'ator_tipo' => $ator->getMorphClass(),
                'ator_id' => $ator->getKey(),
            ],
        ]);
    }

    private function participantesConfirmados(Evento $evento): Builder
    {
        return DB::table('p_ag_inscreve as inscricao')
            ->join('agenda', 'agenda.agenda_id', '=', 'inscricao.agenda_id')
            ->where('agenda.evento_id', $evento->getKey())
            ->where('agenda.status_ativo', 'finalizado')
            ->where('inscricao.status_inscricao', 'participou')
            ->where('inscricao.presenca', true)
            ->whereNotNull('inscricao.presenca_confirmada_em');
    }
}
