<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AvaliacaoOng;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Services\AvaliacaoService;
use App\Services\JustificativaParticipacaoService;
use App\Services\MonitoriaService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class WebParticipationController extends Controller
{
    public function index(
        Request $request,
        AvaliacaoService $avaliacoes,
        JustificativaParticipacaoService $justificativas,
        MonitoriaService $monitoria,
    ) {
        $pessoa = $request->attributes->get('web_account');
        $inscricoesDaPessoa = fn ($query) => $query->where('pessoa_id', $pessoa->getKey())
            ->where(fn (Builder $history) => $history->whereIn('status_inscricao', ['aprovado', 'participando', 'participou', 'não participou', 'desistente'])
                ->orWhereHas('agenda', fn (Builder $agenda) => $agenda->where('status_ativo', 'cancelado')));
        $eventos = Evento::query()
            ->whereHas('agendas.inscricoes', $inscricoesDaPessoa)
            ->with([
                'agendas' => fn ($agendas) => $agendas->whereHas('inscricoes', $inscricoesDaPessoa)->orderBy('data_inicio'),
                'agendas.inscricoes' => fn ($inscricoes) => $inscricoesDaPessoa($inscricoes)->withExists('justificativaParticipacao'),
            ])
            ->withCount(['agendas as agendas_abertas_count' => fn (Builder $query) => $query
                ->whereNotIn('status_ativo', ['finalizado', 'cancelado'])])
            ->orderByDesc('evento_id')
            ->simplePaginate(12);
        $notas = AvaliacaoOng::where('pessoa_id', $pessoa->getKey())
            ->whereIn('evento_id', $eventos->getCollection()->modelKeys())
            ->pluck('nota', 'evento_id');

        $eventos->through(function (Evento $evento) use ($pessoa, $notas, $avaliacoes, $justificativas, $monitoria): array {
            $nota = $notas[$evento->getKey()] ?? null;
            $participou = $evento->agendas->contains(fn ($agenda) => $agenda->status_ativo === 'finalizado'
                && $agenda->inscricoes->contains(fn ($inscricao) => $inscricao->status_inscricao === 'participou'
                    && $inscricao->presenca && $inscricao->presenca_confirmada_em !== null));
            $elegivel = $nota === null && $pessoa->isApprovedVolunteer() && $evento->ong_id !== null
                && $evento->agendas_abertas_count === 0 && $participou && $avaliacoes->aceitaAvaliacaoOng($evento);

            return [
                'titulo' => $evento->nm_evento,
                'status_evento' => $evento->status_evento,
                'nota' => $nota,
                'avaliacao_url' => $elegivel ? route('identity.participation.rate', $evento) : null,
                'avaliacao_ate' => $elegivel ? $avaliacoes->prazoAvaliacaoOng($evento)?->toISOString() : null,
                'agendas' => $evento->agendas->map(fn (Agenda $agenda) => $this->agendaHistorico(
                    $evento, $agenda, $pessoa, $justificativas, $monitoria,
                )),
            ];
        });

        return ApiResponse::success($eventos);
    }

    private function agendaHistorico(
        Evento $evento,
        Agenda $agenda,
        Pessoa $pessoa,
        JustificativaParticipacaoService $justificativas,
        MonitoriaService $monitoria,
    ): array {
        $inscricao = $agenda->inscricoes->first();
        $jaEnviada = $inscricao->justificativa_participacao_exists;
        $podeEnviar = ! $jaEnviada && $justificativas->podeSubmeter($evento, $agenda, $inscricao, $pessoa);

        return [
            'inscricao_id' => $inscricao->getKey(),
            'inicio' => $agenda->data_inicio->toISOString(),
            'situacao' => $agenda->status_ativo === 'cancelado' ? 'Cancelada' : match ($inscricao->status_inscricao) {
                'aprovado' => 'Inscrição aprovada',
                'participando' => 'Participação confirmada',
                'participou' => 'Presença confirmada',
                'não participou' => 'Ausência registrada',
                'desistente' => 'Desistência registrada',
                default => 'Cancelada',
            },
            'justificativa_url' => $jaEnviada || $podeEnviar ? route('identity.management.participation.justification', $inscricao) : null,
            'enviar_justificativa_url' => $podeEnviar ? route('identity.management.participation.justification.submit', $inscricao) : null,
            'monitoria_url' => $monitoria->aplicavel($agenda, $inscricao)
                ? route('identity.management.participation.monitoring', $inscricao) : null,
        ];
    }

    public function rate(Request $request, Evento $evento, AvaliacaoService $avaliacoes)
    {
        $validated = $request->validate(AvaliacaoService::regrasNota());
        $nota = $avaliacoes->registrarOng($evento, $request->attributes->get('web_account'), (int) $validated['nota']);

        return ApiResponse::success(['nota' => $nota->nota], 'Avaliação enviada.', 201);
    }

    public function showRating(Request $request, Evento $evento, AvaliacaoService $avaliacoes)
    {
        $nota = $avaliacoes->consultarOng($evento, $request->attributes->get('web_account'));

        return ApiResponse::success(['nota' => $nota->nota]);
    }
}
