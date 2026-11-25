<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationConflictException;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use App\Models\Ong;
use App\Services\EventoManagementService;
use App\Services\EventoModerationService;
use App\Services\EventoParticipationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EventoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR EVENTOS / FILTRAR POR CATEGORIA
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'categoria' => ['sometimes', 'integer', 'exists:cat_evento,cat_evento_id'],
            'ong' => ['sometimes', 'integer', 'exists:ong,ong_id'],
            'modalidade' => ['sometimes', Rule::in(['presencial', 'online', 'hibrido'])],
            'data_inicio' => ['sometimes', 'date'],
            'data_fim' => ['sometimes', 'date', 'after_or_equal:data_inicio'],
            'q' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Evento::with(['ong', 'pessoa', 'responsavelPessoa', 'categoria'])
            ->publicado();

        if (isset($filters['categoria'])) {
            $query->where('cat_evento_id', $filters['categoria']);
        }

        if (isset($filters['ong'])) {
            $query->where('ong_id', $filters['ong']);
        }

        if (isset($filters['modalidade'])) {
            $query->where('modalidade_evento', $filters['modalidade']);
        }

        if (isset($filters['q'])) {
            $term = $filters['q'];
            $query->where(function ($builder) use ($term) {
                $builder->where('nm_evento', 'like', "%{$term}%")
                    ->orWhere('descricao_evento', 'like', "%{$term}%");
            });
        }

        if (isset($filters['data_inicio'])) {
            $query->whereHas('agendas', fn ($agenda) => $agenda->whereDate('data_inicio', '>=', $filters['data_inicio']));
        }

        if (isset($filters['data_fim'])) {
            $query->whereHas('agendas', fn ($agenda) => $agenda->whereDate('data_fim', '<=', $filters['data_fim']));
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $eventos = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success(EventoResource::collection($eventos));
    }

    /*
    |--------------------------------------------------------------------------
    | DETALHES DE UM EVENTO
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $evento = Evento::with(['ong', 'pessoa', 'responsavelPessoa', 'categoria', 'habilidades', 'agendas'])
            ->publicado()
            ->findOrFail($id);

        return ApiResponse::success(new EventoResource($evento));
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, EventoManagementService $service)
    {
        $ator = $request->user();
        Gate::forUser($ator)->authorize('create', Evento::class);
        $dados = $request->validate(EventoManagementService::regrasCriacao(true, $request->input('modalidade_evento') === 'online'));
        $evento = $service->criar($ator, $dados);

        return ApiResponse::success(new EventoResource($evento), 'Evento criado com sucesso.', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id, EventoManagementService $service)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $evento);
        $online = $request->input('modalidade_evento', $evento->modalidade_evento) === 'online';
        $dados = $request->validate(EventoManagementService::regrasEdicao($online, true));
        $evento = $service->atualizar($request->user(), $evento, $dados);

        return ApiResponse::success(new EventoResource($evento), 'Evento atualizado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR EVENTO
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('delete', $evento);

        DB::transaction(function () use ($evento, $request) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('delete', [$evento, true]);
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode ser excluído.');
            }
            if ($evento->agendas()->exists()) {
                throw new ValidationConflictException('O evento possui agendas e deve ser cancelado para preservar o histórico.');
            }
            $evento->delete();
        });

        return response()->noContent();
    }

    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS — ADMIN
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, $id, EventoModerationService $moderacao)
    {
        $dados = $request->validate(EventoModerationService::regras(false));
        $evento = $moderacao->decidir($request->user(), (int) $id, $dados['status_evento'], $dados['motivo'] ?? null);

        return ApiResponse::success(new EventoResource($evento), 'Status do evento atualizado com sucesso.');
    }

    public function pendentes(Request $request)
    {
        return ApiResponse::success(EventoResource::collection(
            Evento::where('status_evento', 'aguardando a confirmação')
                ->orderBy('created_at')->paginate(min(max($request->integer('per_page', 20), 1), 100))
        ));
    }

    public function cancelar(Request $request, int $id, EventoManagementService $service)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);
        $dados = $request->validate(EventoManagementService::regrasCancelamento());
        $evento = $service->cancelar($request->user(), $evento, $dados['motivo']);

        return ApiResponse::success(new EventoResource($evento), 'Evento cancelado.');
    }

    public function finalizar(Request $request, int $id, EventoParticipationService $participacao)
    {
        $evento = $participacao->finalizarEvento($id, $request->user());

        return ApiResponse::success(new EventoResource($evento), 'Evento finalizado.');
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS DE UMA ONG
    |--------------------------------------------------------------------------
    */

    public function porOng($id)
    {
        Ong::findOrFail($id);

        $eventos = Evento::with('categoria')
            ->where('ong_id', $id)
            ->publicado()
            ->paginate(min(max(request()->integer('per_page', 20), 1), 100));

        return ApiResponse::success(EventoResource::collection($eventos));
    }
}
