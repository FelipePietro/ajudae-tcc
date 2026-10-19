<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Habilidade;
use App\Services\EventoOperationalService;
use App\Exceptions\ValidationConflictException;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class EventoHabilidadeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAR HABILIDADES DO EVENTO
    |--------------------------------------------------------------------------
    */

    public function index($id)
    {
        $evento = Evento::where('status_evento', 'ativo')->findOrFail($id);

        return ApiResponse::success($evento->habilidades()->get());
    }

    /*
    |--------------------------------------------------------------------------
    | ADICIONAR HABILIDADE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id, EventoOperationalService $operations)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);

        $validated = $request->validate([

            'habilidade_id' => [
                'required',
                'integer',
                'exists:habilidade,habilidade_id',
            ],

        ]);

        DB::transaction(function () use ($evento, $validated, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar habilidades.');
            }
            if ($evento->habilidades()->where('habilidade.habilidade_id', $validated['habilidade_id'])->exists()) {
                throw new ValidationConflictException('Esta habilidade já está associada ao evento.');
            }
            $evento->habilidades()->attach($validated['habilidade_id']);
            $operations->exigirReaprovacao($evento);
            $operations->notificarEvento($evento, 'evento.alterado', "As habilidades do evento {$evento->nm_evento} foram alteradas.");
        });

        return ApiResponse::success($evento->habilidades()->get(), 'Habilidade adicionada ao evento com sucesso.', 201);
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVER HABILIDADE
    |--------------------------------------------------------------------------
    */

    public function destroy(Request $request, $id, $hid, EventoOperationalService $operations)
    {
        $evento = Evento::findOrFail($id);
        Gate::forUser($request->user())->authorize('manage', $evento);

        Habilidade::findOrFail($hid);

        DB::transaction(function () use ($evento, $hid, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar habilidades.');
            }
            if (! $evento->habilidades()->where('habilidade.habilidade_id', $hid)->exists()) {
                abort(404);
            }
            $evento->habilidades()->detach($hid);
            $operations->exigirReaprovacao($evento);
            $operations->notificarEvento($evento, 'evento.alterado', "As habilidades do evento {$evento->nm_evento} foram alteradas.");
        });

        return response()->noContent();
    }
}
