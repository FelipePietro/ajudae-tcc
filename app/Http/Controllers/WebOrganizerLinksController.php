<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\VinculoOngOrganizador;
use App\Services\OrganizadorService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebOrganizerLinksController extends Controller
{
    public function index(Request $request)
    {
        $ator = $this->operador($request);
        $instituicao = $ator instanceof Ong;
        $vinculos = VinculoOngOrganizador::query()
            ->where($instituicao ? 'ong_id' : 'pessoa_id', $ator->getKey())
            ->with($instituicao ? 'pessoa:pessoa_id,nm_pessoa,exclusao_pendente' : 'ong:ong_id,nome_fantasia,exclusao_pendente')
            ->orderByRaw("CASE status WHEN 'ativo' THEN 0 WHEN 'convite_pendente' THEN 1 ELSE 2 END")
            ->orderByDesc('vinculo_ong_organizador_id')
            ->simplePaginate(20)
            ->through(function (VinculoOngOrganizador $vinculo) use ($instituicao): array {
                $outraParte = $instituicao ? $vinculo->pessoa : $vinculo->ong;
                $nome = $outraParte?->exclusao_pendente ? null : ($instituicao ? $outraParte?->nm_pessoa : $outraParte?->nome_fantasia);

                return [
                    'nome' => $nome ?? 'Conta indisponível',
                    'status' => $vinculo->status,
                    'responder' => ! $instituicao && $vinculo->status === 'convite_pendente'
                        ? route('identity.organizer-links.respond', $vinculo) : null,
                    'encerrar' => $vinculo->status === 'ativo'
                        ? route('identity.organizer-links.end', $vinculo) : null,
                ];
            });

        return ApiResponse::success($vinculos);
    }

    public function invite(Request $request, OrganizadorService $service)
    {
        $ong = $this->operador($request);
        abort_unless($ong instanceof Ong, 403);
        $data = $request->validate(['identificador' => ['required', 'string', 'max:128']]);
        $service->convidarPorIdentificador($ong, $data['identificador']);

        return ApiResponse::success(null, 'Se o organizador estiver disponível, o convite será registrado.', 202);
    }

    public function respond(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        $pessoa = $this->operador($request);
        abort_unless($pessoa instanceof Pessoa, 403);
        $data = $request->validate(['aceitar' => ['required', 'boolean']]);
        $service->responderConvite($vinculo, $pessoa, (bool) $data['aceitar']);

        return ApiResponse::success(null, $data['aceitar'] ? 'Convite aceito.' : 'Convite recusado.');
    }

    public function end(Request $request, VinculoOngOrganizador $vinculo, OrganizadorService $service)
    {
        $service->encerrarVinculo($vinculo, $this->operador($request));

        return ApiResponse::success(null, 'Vínculo encerrado.');
    }

    private function operador(Request $request): Pessoa|Ong
    {
        $ator = $request->attributes->get('web_account');
        abort_unless(($ator instanceof Ong && AccountAccess::activeOng($ator))
            || ($ator instanceof Pessoa && $ator->role_pessoa === 'organizador'
                && $ator->status_validacao === 'aprovado' && AccountAccess::canOperate($ator)), 403);

        return $ator;
    }
}
