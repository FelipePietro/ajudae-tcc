<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoOrganizador;
use App\Models\VinculoOngOrganizador;
use App\Services\OrganizadorService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OrganizadorController extends Controller
{
    public function solicitar(Request $request, OrganizadorService $service)
    {
        $validated = $request->validate([
            'justificativa' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/'],
        ]);

        return ApiResponse::success(
            $service->solicitar($request->user(), $validated['justificativa']),
            'Solicitação enviada com sucesso.',
            201
        );
    }

    public function status(Request $request)
    {
        abort_unless(AccountAccess::hasPersonalJourney($request->user()), 403);

        $solicitacao = SolicitacaoOrganizador::where('pessoa_id', $request->user()->pessoa_id)
            ->latest('solicitada_em')
            ->first();

        return ApiResponse::success($solicitacao);
    }

    public function convites(Request $request)
    {
        abort_unless(AccountAccess::hasPersonalJourney($request->user()), 403);

        return ApiResponse::success(
            VinculoOngOrganizador::with(['ong' => fn ($query) => $query
                ->select('ong_id', 'nome_fantasia')
                ->where('exclusao_pendente', false)])
                ->where('pessoa_id', $request->user()->pessoa_id)
                ->latest('convidado_em')
                ->get()
        );
    }

    public function aceitarConvite(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->responderConvite($vinculo, $request->user(), true),
            'Convite aceito com sucesso.'
        );
    }

    public function recusarConvite(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->responderConvite($vinculo, $request->user(), false),
            'Convite recusado.'
        );
    }

    public function encerrarVinculo(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        return ApiResponse::success(
            $service->encerrarVinculo($vinculo, $request->user()),
            'Vínculo encerrado com sucesso.'
        );
    }
}
