<?php

namespace App\Http\Controllers;

use App\Http\Resources\InscricaoResource;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Services\CandidaturaService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebApplicationController extends Controller
{
    public function agendas(Request $request, Evento $evento, CandidaturaService $service)
    {
        $pessoa = $request->attributes->get('web_account');
        abort_unless($pessoa->isApprovedVolunteer(), 403);
        abort_unless($evento->status_evento === 'ativo', 404);

        return ApiResponse::success($service->agendas($evento, $pessoa));
    }

    public function store(Request $request, Evento $evento, CandidaturaService $service)
    {
        $pessoa = $request->attributes->get('web_account');
        abort_unless($pessoa->isApprovedVolunteer(), 403);
        $data = $request->validate(['agenda_id' => ['required', 'integer', 'min:1'], ...CandidaturaService::regras()]);
        $inscricao = $service->inscrever($pessoa, (int) $data['agenda_id'], $data, $evento->getKey());

        return ApiResponse::success(new InscricaoResource($inscricao), 'Candidatura enviada para seleção.', 201);
    }

    public function index(Request $request, CandidaturaService $service)
    {
        $pessoa = $request->attributes->get('web_account');
        abort_unless($pessoa->role_pessoa === 'voluntario', 403);

        $inscricoes = Inscricao::where('pessoa_id', $pessoa->getKey())->with('agenda.evento')
            ->orderByDesc('inscricao_id')->simplePaginate(12)
            ->through(fn (Inscricao $inscricao) => [
                'evento' => $inscricao->agenda->evento->nm_evento,
                'inicio' => $inscricao->agenda->data_inicio->toISOString(),
                'status' => $inscricao->agenda->status_ativo === 'cancelado' ? 'cancelada' : $inscricao->status_inscricao,
                'carta' => $inscricao->carta_apresentacao,
                'desistir_url' => $service->podeDesistir($inscricao->agenda->evento, $inscricao->agenda, $inscricao)
                    ? route('identity.application.withdraw', $inscricao) : null,
            ]);

        return ApiResponse::success($inscricoes);
    }

    public function withdraw(Request $request, Inscricao $inscricao, CandidaturaService $service)
    {
        $pessoa = $request->attributes->get('web_account');
        abort_unless($pessoa->role_pessoa === 'voluntario', 403);
        $service->desistir($pessoa, $inscricao);

        return ApiResponse::success(null, 'Desistência registrada.');
    }
}
