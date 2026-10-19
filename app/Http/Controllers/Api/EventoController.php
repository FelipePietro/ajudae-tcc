<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\AdminLog;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\EventoOperationalService;
use App\Exceptions\ValidationConflictException;
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
            ->where('status_evento', 'ativo');

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
            ->where('status_evento', 'ativo')
            ->findOrFail($id);

        return ApiResponse::success(new EventoResource($evento));
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $isOnline = $request->input('modalidade_evento') === 'online';
        $validated = $request->validate([
            'logradouro_evento' => [Rule::requiredIf(! $isOnline), 'nullable', 'string', 'max:64'],
            'cep_evento' => [Rule::requiredIf(! $isOnline), 'nullable', 'digits:8'],
            'cidade_evento' => [Rule::requiredIf(! $isOnline), 'nullable', 'string', 'max:64'],
            'bairro_evento' => [Rule::requiredIf(! $isOnline), 'nullable', 'string', 'max:64'],
            'uf_evento' => [Rule::requiredIf(! $isOnline), 'nullable', 'string', 'size:2'],

            'nm_evento' => ['required', 'string', 'max:64'],
            'descricao_evento' => ['required', 'string', 'max:256'],
            'imagem_evento_link' => ['required', 'string', 'max:255'],

            'cat_evento_id' => [
                'required',
                'integer',
                'exists:cat_evento,cat_evento_id',
            ],

            'compl_evento' => [
                'nullable',
                'string',
                'max:64',
            ],

            'vagas_evento' => [
                'required',
                'integer',
                'min:1',
            ],

            'modalidade_evento' => [
                'required',
                'in:presencial,online,hibrido',
            ],
        ]);

        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | ONG CRIANDO EVENTO
        |--------------------------------------------------------------------------
        */

        $validated['status_evento'] = 'aguardando a confirmação';

        if ($user instanceof Ong) {
            if ($user->status_ong !== 'ativo') {
                return ApiResponse::error('A ONG precisa estar aprovada para criar eventos.', null, 403);
            }
            $evento = new Evento($validated);
            $evento->forceFill([
                'ong_id' => $user->ong_id,
                'pessoa_id' => null,
                'responsavel_pessoa_id' => null,
            ])->save();
        }

        /*
        |--------------------------------------------------------------------------
        | ORGANIZADOR SOLO CRIANDO EVENTO
        |--------------------------------------------------------------------------
        */

        elseif ($user instanceof Pessoa) {
            $evento = DB::transaction(function () use ($user, $validated) {
                $pessoa = Pessoa::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

                if ($pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado') {
                    return null;
                }

                $vinculo = $pessoa->vinculoOngAtivo()->first();
                $evento = new Evento($validated);
                $evento->forceFill([
                    'ong_id' => $vinculo?->ong_id,
                    'pessoa_id' => $vinculo ? null : $pessoa->pessoa_id,
                    'responsavel_pessoa_id' => $pessoa->pessoa_id,
                ])->save();

                return $evento;
            });

            if ($evento === null) {
                return ApiResponse::error('Apenas organizadores aprovados podem criar eventos.', null, 403);
            }
        } else {
            return ApiResponse::error('Usuário não autorizado.', null, 403);
        }

        return ApiResponse::success(new EventoResource($evento), 'Evento criado com sucesso.', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR EVENTO
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id, EventoOperationalService $operations)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('update', $evento);

        $isOnline = $request->input('modalidade_evento', $evento->modalidade_evento) === 'online';

        $validated = $request->validate([
            'logradouro_evento' => [
                'sometimes',
                Rule::requiredIf(! $isOnline),
                'nullable',
                'string',
                'max:64',
            ],

            'cep_evento' => [
                'sometimes',
                Rule::requiredIf(! $isOnline),
                'nullable',
                'digits:8',
            ],

            'cidade_evento' => [
                'sometimes',
                Rule::requiredIf(! $isOnline),
                'nullable',
                'string',
                'max:64',
            ],

            'bairro_evento' => [
                'sometimes',
                Rule::requiredIf(! $isOnline),
                'nullable',
                'string',
                'max:64',
            ],

            'uf_evento' => [
                'sometimes',
                Rule::requiredIf(! $isOnline),
                'nullable',
                'string',
                'size:2',
            ],

            'nm_evento' => [
                'sometimes',
                'string',
                'max:64',
            ],

            'descricao_evento' => [
                'sometimes',
                'string',
                'max:256',
            ],

            'imagem_evento_link' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'cat_evento_id' => [
                'sometimes',
                'integer',
                'exists:cat_evento,cat_evento_id',
            ],

            'compl_evento' => [
                'sometimes',
                'nullable',
                'string',
                'max:64',
            ],

            'vagas_evento' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'modalidade_evento' => [
                'sometimes',
                'in:presencial,online,hibrido',
            ],
        ]);

        if ($isOnline) {
            foreach (['logradouro_evento', 'cep_evento', 'cidade_evento', 'bairro_evento', 'uf_evento', 'compl_evento'] as $field) {
                $validated[$field] = $validated[$field] ?? null;
            }
        }

        $evento = DB::transaction(function () use ($evento, $validated, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode ser alterado.');
            }
            $evento->fill($validated);
            $alterados = array_keys($evento->getDirty());
            if ($alterados === []) {
                return $evento;
            }

            $sensivel = array_intersect($alterados, [
                'descricao_evento', 'logradouro_evento', 'cep_evento', 'cidade_evento',
                'bairro_evento', 'uf_evento', 'compl_evento',
            ]) !== [];
            $localizacao = array_intersect($alterados, [
                'logradouro_evento', 'cep_evento', 'cidade_evento', 'bairro_evento', 'uf_evento', 'compl_evento',
            ]) !== [];
            $evento->save();
            if ($sensivel || $evento->status_evento === 'reprovado') {
                $operations->exigirReaprovacao($evento);
            }
            if ($localizacao) {
                foreach ($evento->agendas as $agenda) {
                    $operations->concederDesistenciaSemPenalidade($agenda);
                }
            }
            $operations->notificarEvento($evento, 'evento.alterado', "O evento {$evento->nm_evento} foi alterado.");

            return $evento;
        });

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

        DB::transaction(function () use ($evento) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
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

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status_evento' => [
                'required',
                Rule::in(['ativo', 'reprovado']),
            ],
        ]);

        $evento = DB::transaction(function () use ($id, $validated, $request) {
            $evento = Evento::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($evento->status_evento !== 'aguardando a confirmação') {
                throw new ValidationConflictException('O evento não está aguardando aprovação.');
            }

            $status = $validated['status_evento'];
            $evento->forceFill(['status_evento' => $status])->save();
            AdminLog::create([
                'pessoa_id' => $request->user()->pessoa_id,
                'acao' => $status === 'ativo' ? 'evento.aprovado' : 'evento.reprovado',
                'recurso_type' => Evento::class,
                'recurso_id' => $evento->evento_id,
                'detalhes' => ['de' => 'aguardando a confirmação', 'para' => $status],
            ]);

            $responsavel = $evento->responsavelPessoa ?? $evento->ong ?? $evento->pessoa;
            $responsavel?->notificacoes()->create([
                'tipo_notificacao' => 'evento.status_atualizado',
                'mensagem_notificacao' => "O status do evento {$evento->nm_evento} foi atualizado.",
                'evento_id' => $evento->evento_id,
            ]);

            return $evento;
        });

        return ApiResponse::success(new EventoResource($evento), 'Status do evento atualizado com sucesso.');
    }

    public function pendentes(Request $request)
    {
        return ApiResponse::success(EventoResource::collection(
            Evento::where('status_evento', 'aguardando a confirmação')
                ->orderBy('created_at')->paginate(min(max($request->integer('per_page', 20), 1), 100))
        ));
    }

    public function cancelar(Request $request, int $id, EventoOperationalService $operations)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/'],
        ]);

        $evento = DB::transaction(function () use ($evento, $request, $validated, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('O evento já está em estado terminal.');
            }
            $ator = $request->user();
            $autorTipo = $ator instanceof Ong ? 'ong' : 'pessoa';
            $evento->forceFill([
                'status_evento' => 'cancelado',
                'motivo_cancelamento' => trim($validated['motivo']),
                'cancelado_em' => now(),
                'cancelado_por_tipo' => $autorTipo,
                'cancelado_por_id' => $ator->getKey(),
            ])->save();
            $evento->agendas()->whereNotIn('status_ativo', ['finalizado', 'cancelado'])->update([
                'status_ativo' => 'cancelado',
                'motivo_cancelamento' => trim($validated['motivo']),
                'cancelado_em' => now(),
                'cancelado_por_tipo' => $autorTipo,
                'cancelado_por_id' => $ator->getKey(),
            ]);
            $operations->notificarEvento($evento, 'evento.cancelado', "O evento {$evento->nm_evento} foi cancelado.");

            return $evento;
        });

        return ApiResponse::success(new EventoResource($evento), 'Evento cancelado.');
    }

    public function finalizar(Request $request, int $id)
    {
        $evento = DB::transaction(function () use ($request, $id) {
            $evento = Evento::whereKey($id)->lockForUpdate()->firstOrFail();
            Gate::forUser($request->user())->authorize('manage', $evento);

            if ($evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('O evento não está ativo para finalização.');
            }

            $agendasPendentes = $evento->agendas()
                ->whereNotIn('status_ativo', ['finalizado', 'cancelado'])->count();
            if ($agendasPendentes > 0) {
                throw new ValidationConflictException("O evento possui {$agendasPendentes} agendas pendentes.");
            }

            $participantes = DB::table('p_ag_inscreve as inscricao')
                ->join('agenda', 'agenda.agenda_id', '=', 'inscricao.agenda_id')
                ->where('agenda.evento_id', $evento->evento_id)
                ->where('agenda.status_ativo', 'finalizado')
                ->where('inscricao.status_inscricao', 'participou')
                ->where('inscricao.presenca', true)
                ->whereNotNull('inscricao.presenca_confirmada_em')
                ->distinct()->pluck('inscricao.pessoa_id');

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

            $evento->forceFill(['status_evento' => 'finalizado'])->save();

            return $evento;
        });

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
            ->where('status_evento', 'ativo')
            ->paginate(min(max(request()->integer('per_page', 20), 1), 100));

        return ApiResponse::success(EventoResource::collection($eventos));
    }
}
