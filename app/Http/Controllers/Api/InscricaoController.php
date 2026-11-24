<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationConflictException;
use App\Http\Controllers\Controller;
use App\Http\Resources\InscricaoResource;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Services\AvaliacaoService;
use App\Services\CandidaturaService;
use App\Services\ConfiabilidadeService;
use App\Services\EventoOperationalService;
use App\Services\MatchService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InscricaoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id, CandidaturaService $candidaturas)
    {
        $pessoa = $request->user();

        if (! ($pessoa instanceof Pessoa) || ! $pessoa->isApprovedVolunteer()) {
            return ApiResponse::error('Apenas voluntários aprovados podem se inscrever em agendas.', null, 403);
        }

        $validated = $request->validate(CandidaturaService::regras());
        $inscricao = $candidaturas->inscrever($pessoa, (int) $id, $validated);

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
        $mostrarCarta = app(EventoPolicy::class)->operate($request->user(), $agenda->evento);
        foreach ($inscricoes->getCollection() as $inscricao) {
            $inscricao->pessoa->setAttribute('media_avaliacao_calculada', $medias[$inscricao->pessoa_id] ?? null);
            $inscricao->pessoa->setAttribute('confiabilidade_resumo', $resumos[$inscricao->pessoa_id]);
            $inscricao->setAttribute('match', $match->calcular($agenda->evento, $inscricao->pessoa));
            $inscricao->setAttribute('confiabilidade', $resumos[$inscricao->pessoa_id]);
            $inscricao->setAttribute('carta_visivel', $mostrarCarta);
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
            ! Gate::forUser($user)->allows('viewPrivateHistory', $pessoa)
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
        $user = $request->user();
        abort_unless($user instanceof Pessoa && AccountAccess::hasPersonalJourney($user), 403);

        return $this->porPessoa($request, $user->pessoa_id);
    }

    public function show(Request $request, int $id)
    {
        $inscricao = Inscricao::with(['agenda.evento', 'pessoa'])->findOrFail($id);
        $user = $request->user();

        if ($user instanceof Pessoa && AccountAccess::hasPersonalJourney($user) && $user->pessoa_id === $inscricao->pessoa_id) {
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

        DB::transaction(function () use ($inscricao, $target, $transitions, $operations, $request) {
            $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('operate', [$evento, true]);
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo' || $evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O Evento e a Agenda precisam estar ativos para alterar a seleção.');
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

        $inscricao = DB::transaction(function () use ($inscricao, $validated, $request) {
            $evento = Evento::whereKey($inscricao->agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('operate', [$evento, true]);
            $agenda = Agenda::whereKey($inscricao->agenda_id)->lockForUpdate()->firstOrFail();
            $locked = Inscricao::whereKey($inscricao->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'ativo' || $evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O Evento e a Agenda precisam estar ativos para registrar presença ou ausência.');
            }
            if (now()->lessThan($agenda->data_inicio)) {
                throw new ValidationConflictException('A presença só pode ser registrada após o início da agenda.');
            }
            if (! in_array($locked->status_inscricao, ['aprovado', 'participando'], true)
                || $locked->presenca_confirmada_em !== null) {
                throw new ValidationConflictException('A inscrição não está apta a receber presença ou ausência.');
            }

            $duracao = round($agenda->data_inicio->diffInSeconds($agenda->data_fim, false) / 3600, 2);
            $autor = $request->user();
            $locked->forceFill([
                'presenca' => $validated['presenca'],
                'presenca_confirmada_em' => now(),
                'presenca_confirmada_por_tipo' => $autor instanceof Ong ? 'ong' : 'pessoa',
                'presenca_confirmada_por_id' => $autor->getKey(),
                'carga_horaria' => $validated['presenca'] ? $duracao : null,
                'status_inscricao' => $validated['presenca'] ? 'participou' : 'não participou',
            ])->save();

            return $locked;
        });

        return ApiResponse::success(new InscricaoResource($inscricao), 'Presença registrada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELAR INSCRIÇÃO
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id, CandidaturaService $candidaturas)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);

        $user = $request->user();

        if (
            ! ($user instanceof Pessoa) ||
            ! AccountAccess::hasPersonalJourney($user) ||
            $user->pessoa_id !== $inscricao->pessoa_id
        ) {
            return ApiResponse::error('Você não tem permissão para cancelar esta inscrição.', null, 403);
        }

        $inscricao = $candidaturas->desistir($user, $inscricao);

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
