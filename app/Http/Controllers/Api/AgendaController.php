<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Ong;
use App\Exceptions\ValidationConflictException;
use App\Services\EventoOperationalService;
use App\Services\XpService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        Evento::where('status_evento', 'ativo')->findOrFail($evento);

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
    public function store(Request $request, EventoOperationalService $operations, ?int $evento = null)
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio',
            'vagas_agenda' => 'required|integer|min:1',
            'evento_id' => [$evento ? 'nullable' : 'required', 'integer', 'exists:evento,evento_id'],
        ]);

        $this->validarDuracao($validated['data_inicio'], $validated['data_fim']);

        $eventoModel = Evento::findOrFail($evento ?? $validated['evento_id']);
        Gate::forUser($request->user())->authorize('manage', $eventoModel);
        $validated['evento_id'] = $eventoModel->evento_id;

        $agenda = DB::transaction(function () use ($eventoModel, $validated, $operations) {
            $eventoModel = Evento::whereKey($eventoModel->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($eventoModel->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode receber nova agenda.');
            }
            $agenda = Agenda::create($validated);
            $operations->exigirReaprovacao($eventoModel);
            $operations->notificarEvento($eventoModel, 'evento.alterado', "A estrutura de agendas do evento {$eventoModel->nm_evento} foi alterada.");

            return $agenda;
        });

        return ApiResponse::success($agenda, 'Agenda criada com sucesso.', 201);
    }

    /**
     * Atualizar agenda
     */
    public function update(Request $request, $id, EventoOperationalService $operations)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);

        $validated = $request->validate([
            'data_inicio' => 'sometimes|date',
            'data_fim' => 'sometimes|date',
            'vagas_agenda' => 'sometimes|integer|min:1',
        ]);

        $agenda = DB::transaction(function () use ($agenda, $validated, $operations) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || in_array($agenda->status_ativo, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Agenda terminal não pode ser alterada.');
            }
            $this->validarDuracao(
                $validated['data_inicio'] ?? $agenda->data_inicio,
                $validated['data_fim'] ?? $agenda->data_fim
            );
            $agenda->fill($validated);
            $alterados = array_keys($agenda->getDirty());
            if ($alterados === []) {
                return $agenda;
            }
            if (isset($validated['vagas_agenda']) && $validated['vagas_agenda'] < $operations->vagasOcupadas($agenda)) {
                throw new ValidationConflictException('As vagas não podem ser menores que as já ocupadas.');
            }
            $dataAlterada = array_intersect($alterados, ['data_inicio', 'data_fim']) !== [];
            $agenda->save();
            if ($dataAlterada) {
                $operations->concederDesistenciaSemPenalidade($agenda);
            }
            $operations->exigirReaprovacao($evento);
            $operations->notificarAgenda($agenda, 'agenda.alterada', 'A agenda do evento foi alterada.');

            return $agenda;
        });

        return ApiResponse::success($agenda, 'Agenda atualizada com sucesso.');
    }

    /**
     * Deletar agenda
     */
    public function destroy(Request $request, $id, EventoOperationalService $operations)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('delete', $agenda);

        DB::transaction(function () use ($agenda, $operations) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || in_array($agenda->status_ativo, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Agenda terminal não pode ser excluída.');
            }
            if ($agenda->inscricoes()->exists()) {
                throw new ValidationConflictException('A agenda possui inscrições e deve ser cancelada para preservar o histórico.');
            }
            $agenda->delete();
            $operations->exigirReaprovacao($evento);
            $operations->notificarEvento($evento, 'evento.alterado', "A estrutura de agendas do evento {$evento->nm_evento} foi alterada.");
        });

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
    public function finalizar(Request $request, $id, XpService $xp)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);

        $agenda = DB::transaction(function () use ($agenda, $xp) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || $agenda->status_ativo !== 'ativo') {
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
            $xp->concederAgenda($agenda);

            return $agenda;
        });

        return ApiResponse::success($agenda, 'Agenda finalizada com sucesso.');
    }

    public function consolidarXp(Request $request, int $id, XpService $xp)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $agenda);

        return ApiResponse::success(
            ['transacoes_criadas' => $xp->concederAgenda($agenda)],
            'XP da Agenda consolidado.'
        );
    }

    public function cancelar(Request $request, int $id, EventoOperationalService $operations)
    {
        $agenda = Agenda::with('evento')->findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $agenda->evento);
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/'],
        ]);

        $agenda = DB::transaction(function () use ($agenda, $request, $validated, $operations) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)
                || $agenda->status_ativo !== 'ativo') {
                throw new ValidationConflictException('A agenda não está ativa.');
            }
            $ator = $request->user();
            $agenda->forceFill([
                'status_ativo' => 'cancelado',
                'motivo_cancelamento' => trim($validated['motivo']),
                'cancelado_em' => now(),
                'cancelado_por_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'cancelado_por_id' => $ator->getKey(),
            ])->save();
            $operations->notificarAgenda($agenda, 'agenda.cancelada', 'Uma agenda do evento foi cancelada.');

            return $agenda;
        });

        return ApiResponse::success($agenda, 'Agenda cancelada.');
    }

    private function validarDuracao(Carbon|string $inicio, Carbon|string $fim): void
    {
        if (Carbon::parse($inicio)->diffInMinutes(Carbon::parse($fim), false) < 30) {
            throw ValidationException::withMessages(['data_fim' => 'A agenda deve durar ao menos 30 minutos.']);
        }
    }
}
