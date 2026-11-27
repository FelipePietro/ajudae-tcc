<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationConflictException;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Evento;
use App\Services\AgendaManagementService;
use App\Services\EventoManagementService;
use App\Services\EventoParticipationService;
use App\Services\XpService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AgendaController extends Controller
{
    /**
     * Listar todas as agendas
     */
    public function index(Request $request)
    {
        $agendas = Agenda::with('evento')
            ->whereHas('evento', fn ($query) => $query->where('status_evento', 'ativo'))
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success($agendas);
    }

    public function porEvento(Request $request, int $evento)
    {
        Evento::publicado()->findOrFail($evento);

        $agendas = Agenda::where('evento_id', $evento)
            ->orderBy('data_inicio')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100));

        return ApiResponse::success($agendas);
    }

    /**
     * Exibir uma agenda específica
     */
    public function show($id)
    {
        return ApiResponse::success(Agenda::with('evento')
            ->whereHas('evento', fn ($query) => $query->where('status_evento', 'ativo'))
            ->findOrFail($id));
    }

    /**
     * Criar nova agenda
     */
    public function store(Request $request, AgendaManagementService $service, ?int $evento = null)
    {
        $dados = $request->validate([
            ...AgendaManagementService::regras(true),
            'evento_id' => [$evento ? 'nullable' : 'required', 'integer', 'exists:evento,evento_id'],
        ]);
        $eventoModel = Evento::findOrFail($evento ?? $dados['evento_id']);
        Gate::forUser($request->user())->authorize('createAgenda', $eventoModel);
        $agenda = $service->criar($request->user(), $eventoModel, $dados);

        return ApiResponse::success($agenda, 'Agenda criada com sucesso.', 201);
    }

    /**
     * Atualizar agenda
     */
    public function update(Request $request, $id, AgendaManagementService $service)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);
        $dados = $request->validate(AgendaManagementService::regras(false));
        $agenda = $service->atualizar($request->user(), $agenda, $dados);

        return ApiResponse::success($agenda, 'Agenda atualizada com sucesso.');
    }

    /**
     * Deletar agenda
     */
    public function destroy(Request $request, $id, AgendaManagementService $service)
    {
        $agenda = Agenda::findOrFail($id);
        $service->excluir($request->user(), $agenda);

        return response()->noContent();
    }

    /**
     * Rota legada de ativação; não há estado reativável no schema atual.
     */
    public function ativar(Request $request, $id)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);

        if ($agenda->status_ativo === 'ativo') {
            throw new ValidationConflictException('A Agenda já está ativa.');
        }

        throw new ValidationConflictException('Agenda terminal não pode ser reativada.');
    }

    /**
     * Finalizar agenda (alterar status para finalizado)
     */
    public function finalizar(Request $request, $id, EventoParticipationService $participacao)
    {
        $agenda = $participacao->finalizarAgenda(Agenda::findOrFail($id), $request->user());

        return ApiResponse::success($agenda, 'Agenda finalizada com sucesso.');
    }

    public function consolidarXp(Request $request, int $id, XpService $xp)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);

        $created = DB::transaction(function () use ($agenda, $request, $xp) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('operate', [$evento, true]);

            return $xp->concederAgenda($agenda);
        }, 3);

        return ApiResponse::success(['transacoes_criadas' => $created], 'XP da Agenda consolidado.');
    }

    public function cancelar(Request $request, int $id, AgendaManagementService $service)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $agenda);
        $dados = $request->validate(EventoManagementService::regrasCancelamento());
        $agenda = $service->cancelar($request->user(), $agenda, $dados['motivo']);

        return ApiResponse::success($agenda, 'Agenda cancelada.');
    }
}
