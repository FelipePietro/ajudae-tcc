<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationConflictException;
use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Services\EventoOperationalService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventoAssociacaoController extends Controller
{
    public function causas(int $id)
    {
        return ApiResponse::success(Evento::where('status_evento', 'ativo')->findOrFail($id)->causas()->get());
    }

    public function recursos(int $id)
    {
        return ApiResponse::success(Evento::where('status_evento', 'ativo')->findOrFail($id)->recursos()->get());
    }

    public function adicionarCausa(Request $request, int $id, EventoOperationalService $operations)
    {
        return $this->adicionar($request, $id, $operations, 'causas', 'causa_id', 'causa');
    }

    public function adicionarRecurso(Request $request, int $id, EventoOperationalService $operations)
    {
        return $this->adicionar($request, $id, $operations, 'recursos', 'recurso_id', 'recurso');
    }

    public function removerCausa(Request $request, int $id, int $causa, EventoOperationalService $operations)
    {
        return $this->remover($request, $id, $operations, 'causas', 'causa', 'causa_id', $causa);
    }

    public function removerRecurso(Request $request, int $id, int $recurso, EventoOperationalService $operations)
    {
        return $this->remover($request, $id, $operations, 'recursos', 'recurso', 'recurso_id', $recurso);
    }

    private function adicionar(Request $request, int $id, EventoOperationalService $operations, string $relacao, string $campo, string $tabela)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);
        $validated = $request->validate([$campo => ['required', 'integer', "exists:{$tabela},{$campo}"]]);

        DB::transaction(function () use ($evento, $operations, $relacao, $campo, $tabela, $validated) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar associações.');
            }
            if ($evento->{$relacao}()->where("{$tabela}.{$campo}", $validated[$campo])->exists()) {
                throw new ValidationConflictException('Esta associação já existe.');
            }
            $evento->{$relacao}()->attach($validated[$campo]);
            $operations->exigirReaprovacao($evento);
            $operations->notificarEvento($evento, 'evento.alterado', "As associações do evento {$evento->nm_evento} foram alteradas.");
        });

        return ApiResponse::success($evento->{$relacao}()->get(), 'Associação adicionada.', 201);
    }

    private function remover(Request $request, int $id, EventoOperationalService $operations, string $relacao, string $tabela, string $campo, int $valor)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);

        DB::transaction(function () use ($evento, $operations, $relacao, $tabela, $campo, $valor) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar associações.');
            }
            if (! $evento->{$relacao}()->where("{$tabela}.{$campo}", $valor)->exists()) {
                abort(404);
            }
            $evento->{$relacao}()->detach($valor);
            $operations->exigirReaprovacao($evento);
            $operations->notificarEvento($evento, 'evento.alterado', "As associações do evento {$evento->nm_evento} foram alteradas.");
        });

        return response()->noContent();
    }
}
