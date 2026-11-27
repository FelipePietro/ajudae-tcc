<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InscricaoResource;
use App\Models\Agenda;
use App\Models\Inscricao;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use App\Services\CandidaturaService;
use App\Services\EventoParticipationService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

    public function index(Request $request, $id, EventoParticipationService $participacao)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        $ator = $request->user();
        if (! $this->podeGerenciarAgenda($ator, $agenda)) {
            return ApiResponse::error('Você não tem permissão para visualizar os inscritos desta agenda.', null, 403);
        }

        $dados = $request->validate(EventoParticipationService::regrasConsultaInscritos());
        $inscricoes = $participacao->listarInscritos(
            $agenda,
            $ator,
            $dados['status'] ?? null,
            min(max($request->integer('per_page', 20), 1), 100),
        );

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

    public function updateStatus(Request $request, $id, EventoParticipationService $participacao)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);
        $ator = $request->user();
        if (! $this->podeGerenciarAgenda($ator, $inscricao->agenda)) {
            return ApiResponse::error('Você não tem permissão para alterar esta inscrição.', null, 403);
        }

        $dados = $request->validate(EventoParticipationService::regrasSelecao());
        $inscricao = $participacao->selecionar($inscricao, $ator, $dados['status_inscricao']);

        return ApiResponse::success(new InscricaoResource($inscricao), 'Status da inscrição atualizado com sucesso.');
    }

    public function presenca(Request $request, int $id, EventoParticipationService $participacao)
    {
        $inscricao = Inscricao::with('agenda.evento')->findOrFail($id);
        $ator = $request->user();
        if (! $this->podeGerenciarAgenda($ator, $inscricao->agenda)) {
            return ApiResponse::error('Você não tem permissão para registrar esta presença.', null, 403);
        }

        $dados = $request->validate(EventoParticipationService::regrasPresenca());
        $inscricao = $participacao->registrarPresenca($inscricao, $ator, (bool) $dados['presenca']);

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
