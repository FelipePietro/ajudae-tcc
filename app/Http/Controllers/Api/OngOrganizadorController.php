<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use App\Services\OrganizadorService;
use App\Services\EventoOperationalService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OngOrganizadorController extends Controller
{
    public function convidar(Request $request, OrganizadorService $service)
    {
        $validated = $request->validate([
            'pessoa_id' => ['required', 'integer', 'exists:pessoa,pessoa_id'],
        ]);

        return ApiResponse::success(
            $service->convidar($request->user(), Pessoa::findOrFail($validated['pessoa_id'])),
            'Convite enviado com sucesso.',
            201
        );
    }

    public function index(Request $request)
    {
        return ApiResponse::success(
            VinculoOngOrganizador::with('pessoa')
                ->where('ong_id', $request->user()->ong_id)
                ->latest('convidado_em')
                ->get()
        );
    }

    public function encerrar(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->encerrarVinculo($vinculo, $request->user()),
            'Vínculo encerrado com sucesso.'
        );
    }

    public function atribuirResponsavel(Request $request, Evento $evento, OrganizadorService $service, EventoOperationalService $operations)
    {
        $validated = $request->validate([
            'pessoa_id' => ['nullable', 'integer', 'exists:pessoa,pessoa_id'],
        ]);

        $pessoa = isset($validated['pessoa_id']) ? Pessoa::findOrFail($validated['pessoa_id']) : null;

        return ApiResponse::success(
            $service->atribuirResponsavel($evento, $request->user(), $pessoa, $operations),
            'Responsável operacional atualizado.'
        );
    }
}
