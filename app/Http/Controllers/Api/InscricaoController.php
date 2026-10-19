<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InscricaoResource;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Services\EventoOperationalService;
use App\Services\ConfiabilidadeService;
use App\Services\AvaliacaoService;
use App\Services\MatchService;
use App\Exceptions\ValidationConflictException;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InscricaoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id, EventoOperationalService $operations)
    {
        $agenda = Agenda::findOrFail($id);

        if (! $agenda->isAtivo()) {
            return ApiResponse::error('Esta agenda não está disponível para inscrições.', null, 409);
        }

        $pessoa = $request->user();

        if (! ($pessoa instanceof Pessoa) || ! $pessoa->isApprovedVolunteer()) {
            return ApiResponse::error('Apenas voluntários aprovados podem se inscrever em agendas.', null, 403);
        }

        $inscricao = DB::transaction(function () use ($agenda, $pessoa, $operations) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if ($evento->status_evento !== 'ativo' || ! $agenda->isAtivo()) {
                throw new ValidationConflictException('O evento ou a agenda não está disponível para candidaturas.');
            }
            if (now()->greaterThanOrEqualTo($agenda->data_inicio->copy()->subHour())) {
                throw new ValidationConflictException('A candidatura fecha uma hora antes do início da agenda.');
            }
            if ($agenda->inscricoes()->where('pessoa_id', $pessoa->pessoa_id)->exists()) {
                throw new ValidationConflictException('Você já possui uma inscrição nesta agenda.');
            }
            if ($operations->vagasOcupadas($agenda) >= $agenda->vagas_agenda) {
                throw new ValidationConflictException('A agenda já está lotada.');
            }
            $inscricao = Inscricao::create([
                'agenda_id' => $agenda->agenda_id,
                'pessoa_id' => $pessoa->pessoa_id,
                'status_inscricao' => 'pendente',
                'dt_inscricao' => now(),
            ]);

            $responsavel = $evento->responsavelPessoa ?? $evento->ong ?? $evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.criada',
                'mensagem_notificacao' => "Nova inscrição no evento {$evento->nm_evento}.",
                'agenda_id' => $agenda->agenda_id,
                'evento_id' => $agenda->evento_id,
            ]);

            return $inscricao;
        });

        return ApiResponse::success(new InscricaoResource($inscricao), 'Inscrição realizada com sucesso e enviada para aprovação.', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | LISTAR INSCRITOS DE UMA AGENDA
    |--------------------------------------------------------------------------
    */

    public function index(Request $request, $id, MatchService $match, ConfiabilidadeService $confiabilidade, AvaliacaoService $avaliacoes)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);

        if (! $this->podeGerenciarAgenda($request->user(), $agenda)) {
            return ApiResponse::error('Você não tem permissão para visualizar os inscritos desta agenda.', null, 403);
        }

        $validated = $request->validate([
            'status' => [
                'sometimes',
                Rule::in([
                    'pendente',
                    'aprovado',
                    'reprovado',
                    'participando',
                    'participou',
                    'não participou',
                ]),
            ],
        ]);

        $agenda->evento->loadMissing(['habilidades', 'causas', 'recursos']);
        $query = Inscricao::with(['pessoa.habilidades', 'pessoa.causas', 'pessoa.recursos'])
            ->where('agenda_id', $agenda->agenda_id);

        if (isset($validated['status'])) {
            $query->where(
                'status_inscricao',
                $validated['status']
            );
        }

        $inscricoes = $query->paginate(min(max($request->integer('per_page', 20), 1), 100));
        $ids = $inscricoes->getCollection()->pluck('pessoa_id');
        $resumos = $confiabilidade->resumosPublicosPorPessoas($ids);
        $medias = $avaliacoes->mediasPorPessoas($ids);
        foreach ($inscricoes->getCollection() as $inscricao) {
            $inscricao->pessoa->setAttribute('media_avaliacao_calculada', $medias[$inscricao->pessoa_id] ?? null);
            $inscricao->pessoa->setAttribute('confiabilidade_resumo', $resumos[$inscricao->pessoa_id]);
            $inscricao->setAttribute('match', $match->calcular($agenda->evento, $inscricao->pessoa));
            $inscricao->setAttribute('confiabilidade', $resumos[$inscricao->pessoa_id]);
        }

        return ApiResponse::success(InscricaoResource::collection($inscricoes));
    }

    /*
    |--------------------------------------------------------------------------
    | INSCRIÇÕES DE UMA PESSOA
    |--------------------------------------------------------------------------
    */

    public function porPessoa(Request $request, $id)
    {
        $pessoa = Pessoa::findOrFail($id);

        $user = $request->user();

        if (
            ! ($user instanceof Pessoa) ||
            (
                $user->pessoa_id !== $pessoa->pessoa_id &&
                $user->role_pessoa !== 'administrador'
            )
        ) {
            return ApiResponse::error('Você não tem permissão para visualizar estas inscrições.', null, 403);
        }

        $inscricoes = Inscricao::with([
            'agenda.evento',
        ])
            ->where('pessoa_id', $pessoa->pessoa_id)
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success(InscricaoResource::collection($inscricoes));
    }

    public function minhas(Request $request)
    {
        return $this->porPessoa($request, $request->user()->pessoa_id);
    }

    public function show(Request $request, int $id)
    {
        $inscricao = Inscricao::with(['agenda.evento', 'pessoa'])->findOrFail($id);
        $user = $request->user();

        if ($user instanceof Pessoa && $user->pessoa_id === $inscricao->pessoa_id) {
            return ApiResponse::success(new InscricaoResource($inscricao));
        }

        if (! $this->podeGerenciarAgenda($user, $inscricao->agenda)) {
            return ApiResponse::error('Você não tem permissão para visualizar esta inscrição.', null, 403);
        }

        return ApiResponse::success(new InscricaoResource($inscricao));
    }

    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, $id, EventoOperationalService $operations)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        if (! $this->podeGerenciarAgenda(
            $request->user(),
            $inscricao->agenda
        )) {
            return ApiResponse::error('Você não tem permissão para alterar esta inscrição.', null, 403);
        }

        $validated = $request->validate([
            'status_inscricao' => [
                'required',
                Rule::in([
                    'aprovado',
                    'reprovado',
                    'participando',
                ]),
            ],
        ]);

        $transitions = [
            'pendente' => ['aprovado', 'reprovado'],
            'aprovado' => ['participando'],
        ];

        $target = $validated['status_inscricao'];
        if (! in_array($target, $transitions[$inscricao->status_inscricao] ?? [], true)) {
            throw ValidationException::withMessages([
                'status_inscricao' => 'Transição de status inválida.',
            ]);
        }

        DB::transaction(function () use ($inscricao, $target, $transitions, $operations) {
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo'
                || in_array($agenda->evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Inscrições de agenda terminal não podem ser alteradas.');
            }
            if (! in_array($target, $transitions[$locked->status_inscricao] ?? [], true)) {
                throw new ValidationConflictException('Transição de inscrição inválida.');
            }

            if ($target === 'aprovado') {
                if ($operations->vagasOcupadas($agenda) >= $agenda->vagas_agenda) {
                    throw new ValidationConflictException('Não há vagas disponíveis para aprovação.');
                }
            }

            $locked->update(['status_inscricao' => $target]);
            $locked->pessoa->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.status_atualizado',
                'mensagem_notificacao' => "Sua inscrição foi atualizada para {$target}.",
                'agenda_id' => $locked->agenda_id,
                'evento_id' => $locked->agenda->evento_id,
            ]);
        });

        return ApiResponse::success(new InscricaoResource($inscricao->fresh()), 'Status da inscrição atualizado com sucesso.');
    }

    public function presenca(Request $request, int $id)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        if (! $this->podeGerenciarAgenda($request->user(), $inscricao->agenda)) {
            return ApiResponse::error('Você não tem permissão para registrar esta presença.', null, 403);
        }

        $validated = $request->validate([
            'presenca' => ['required', 'boolean'],
            'carga_horaria' => ['prohibited'],
            'observacao' => ['prohibited'],
        ]);

        $inscricao = DB::transaction(function () use ($inscricao, $validated) {
            $agenda = Agenda::with('evento')->whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo'
                || in_array($agenda->evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Agenda terminal não recebe presença ou ausência.');
            }
            if (now()->lessThan($agenda->data_inicio)) {
                throw new ValidationConflictException('A presença só pode ser registrada após o início da agenda.');
            }
            if (! in_array($locked->status_inscricao, ['aprovado', 'participando'], true)
                || $locked->presenca_confirmada_em !== null) {
                throw new ValidationConflictException('A inscrição não está apta a receber presença ou ausência.');
            }

            $duracao = round($agenda->data_inicio->diffInSeconds($agenda->data_fim, false) / 3600, 2);
            $locked->update([
                'presenca' => $validated['presenca'],
                'presenca_confirmada_em' => now(),
                'carga_horaria' => $validated['presenca'] ? $duracao : null,
                'status_inscricao' => $validated['presenca'] ? 'participou' : 'não participou',
            ]);

            return $locked;
        });

        return ApiResponse::success(new InscricaoResource($inscricao), 'Presença registrada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELAR INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        $user = $request->user();

        if (
            ! ($user instanceof Pessoa) ||
            $user->pessoa_id !== $inscricao->pessoa_id
        ) {
            return ApiResponse::error('Você não tem permissão para cancelar esta inscrição.', null, 403);
        }

        $inscricao = DB::transaction(function () use ($inscricao) {
            $agenda = Agenda::with('evento')->whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if (now()->greaterThanOrEqualTo($agenda->data_inicio)) {
                throw new ValidationConflictException('Não é possível desistir após o início da agenda.');
            }
            if (! in_array($locked->status_inscricao, ['pendente', 'aprovado', 'participando'], true)
                || $locked->presenca_confirmada_em !== null) {
                throw new ValidationConflictException('Esta inscrição não pode mais ser cancelada.');
            }
            if ($agenda->status_ativo === 'finalizado' || $agenda->evento->status_evento === 'finalizado') {
                throw new ValidationConflictException('Inscrição de agenda finalizada não pode ser alterada.');
            }

            $late = now()->diffInSeconds($agenda->data_inicio, false) < 24 * 3600;
            $semPenalidade = ($locked->desistencia_sem_penalidade_ate !== null
                    && now()->lessThanOrEqualTo($locked->desistencia_sem_penalidade_ate))
                || $agenda->status_ativo === 'cancelado'
                || $agenda->evento->status_evento === 'cancelado';
            $locked->update([
                'status_inscricao' => 'desistente',
                'desistiu_em' => now(),
                'desistencia_tardia' => $late,
                'desistencia_sem_penalidade' => $semPenalidade,
            ]);

            $responsavel = $agenda->evento->responsavelPessoa ?? $agenda->evento->ong ?? $agenda->evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'inscricao.desistente',
                'mensagem_notificacao' => "Uma pessoa desistiu da agenda do evento {$agenda->evento->nm_evento}.",
                'agenda_id' => $agenda->agenda_id,
                'evento_id' => $agenda->evento_id,
            ]);

            return $locked;
        });

        return ApiResponse::success(new InscricaoResource($inscricao), 'Desistência registrada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR RESPONSÁVEL PELA AGENDA
    |--------------------------------------------------------------------------
    */

    private function podeGerenciarAgenda($user, Agenda $agenda): bool
    {
        return $agenda->evento
            ? app(EventoPolicy::class)->manage($user, $agenda->evento)
            : false;
    }
}
