<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventoResource;
use App\Http\Resources\InscricaoResource;
use App\Http\Resources\JustificativaParticipacaoResource;
use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\AvaliacaoService;
use App\Services\EventoParticipationService;
use App\Services\JustificativaParticipacaoService;
use App\Services\MonitoriaService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebEventParticipationController extends Controller
{
    public function candidates(Request $request, Agenda $agenda, EventoParticipationService $service)
    {
        $data = $request->validate(EventoParticipationService::regrasConsultaInscritos());
        $inscricoes = $service->listarInscritos(
            $agenda,
            $this->actor($request),
            $data['status'] ?? null,
            min(max($request->integer('per_page', 20), 1), 100),
        );
        $inscricoes->through(fn (Inscricao $inscricao) => new InscricaoResource($inscricao))->withQueryString();

        return ApiResponse::success($inscricoes);
    }

    public function select(Request $request, Inscricao $inscricao, EventoParticipationService $service)
    {
        $data = $request->validate(EventoParticipationService::regrasSelecao());
        $selecionada = $service->selecionar($inscricao, $this->actor($request), $data['status_inscricao']);

        return ApiResponse::success(new InscricaoResource($selecionada), 'Status da inscrição atualizado com sucesso.');
    }

    public function attendance(Request $request, Inscricao $inscricao, EventoParticipationService $service)
    {
        $data = $request->validate(EventoParticipationService::regrasPresenca());
        $atualizada = $service->registrarPresenca($inscricao, $this->actor($request), (bool) $data['presenca']);

        return ApiResponse::success(new InscricaoResource($atualizada), 'Presença registrada com sucesso.');
    }

    public function volunteerRatings(Request $request, Evento $evento, EventoParticipationService $service)
    {
        return ApiResponse::success($service->consultarAvaliacoesVoluntarios(
            $evento,
            $this->actor($request),
            min(max($request->integer('per_page', 20), 1), 100),
        ));
    }

    public function volunteerRating(Request $request, Evento $evento, Pessoa $pessoa, AvaliacaoService $service)
    {
        $data = $request->validate(AvaliacaoService::regrasNota());
        $avaliacao = $service->registrarVoluntario($evento, $pessoa, $this->actor($request), (int) $data['nota']);

        return ApiResponse::success([
            'avaliacao_voluntario_id' => $avaliacao->getKey(),
            'evento_id' => $avaliacao->evento_id,
            'pessoa_id' => $avaliacao->pessoa_id,
            'nota' => $avaliacao->nota,
        ], 'Avaliação registrada.', 201);
    }

    public function monitoring(Request $request, Inscricao $inscricao, MonitoriaService $service)
    {
        return ApiResponse::success($service->consultar($inscricao, $this->actor($request)));
    }

    public function decideMonitoring(Request $request, Inscricao $inscricao, MonitoriaService $service)
    {
        $data = $request->validate(MonitoriaService::regrasDecisao());
        $resultado = $service->decidir($inscricao, $this->actor($request), (bool) $data['confirmada'], $data['justificativa'] ?? null);

        return ApiResponse::success($resultado, 'Decisão de monitoria registrada.');
    }

    public function justification(Request $request, Inscricao $inscricao, JustificativaParticipacaoService $service)
    {
        $justificativa = $service->consultar($inscricao, $this->actor($request));

        return ApiResponse::success(new JustificativaParticipacaoResource($justificativa));
    }

    public function submitJustification(Request $request, Inscricao $inscricao, JustificativaParticipacaoService $service)
    {
        $data = $request->validate(JustificativaParticipacaoService::regrasEnvio());
        $justificativa = $service->submeter($inscricao, $this->actor($request), $data['motivo']);

        return ApiResponse::success(new JustificativaParticipacaoResource($justificativa), 'Justificativa enviada.', 201);
    }

    public function decideJustification(Request $request, Inscricao $inscricao, JustificativaParticipacaoService $service)
    {
        $data = $request->validate(JustificativaParticipacaoService::regrasDecisao());
        $justificativa = $service->decidir($inscricao, $this->actor($request), (bool) $data['deferida'], $data['motivo']);

        return ApiResponse::success(new JustificativaParticipacaoResource($justificativa), 'Decisão da justificativa registrada.');
    }

    public function finishAgenda(Request $request, Agenda $agenda, EventoParticipationService $service)
    {
        $resultado = $service->finalizarAgenda($agenda, $this->actor($request));

        return ApiResponse::success($resultado, 'Agenda finalizada com sucesso.');
    }

    public function finishEvent(Request $request, Evento $evento, EventoParticipationService $service)
    {
        $resultado = $service->finalizarEvento($evento->getKey(), $this->actor($request));

        return ApiResponse::success(new EventoResource($resultado), 'Evento finalizado.');
    }

    private function actor(Request $request): Pessoa|Ong
    {
        return $request->attributes->get('web_account');
    }
}
