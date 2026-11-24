<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoOng;
use App\Models\Evento;
use App\Services\AvaliacaoService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class WebParticipationController extends Controller
{
    public function index(Request $request, AvaliacaoService $avaliacoes)
    {
        $pessoa = $request->attributes->get('web_account');
        $inscricoesDaPessoa = fn ($query) => $query->where('pessoa_id', $pessoa->getKey())
            ->where(fn (Builder $history) => $history->whereIn('status_inscricao', ['participou', 'não participou', 'desistente'])
                ->orWhereHas('agenda', fn (Builder $agenda) => $agenda->where('status_ativo', 'cancelado')));
        $eventos = Evento::query()
            ->whereHas('agendas.inscricoes', $inscricoesDaPessoa)
            ->with([
                'agendas' => fn ($agendas) => $agendas->whereHas('inscricoes', $inscricoesDaPessoa)->orderBy('data_inicio'),
                'agendas.inscricoes' => $inscricoesDaPessoa,
            ])
            ->withCount(['agendas as agendas_abertas_count' => fn (Builder $query) => $query
                ->whereNotIn('status_ativo', ['finalizado', 'cancelado'])])
            ->orderByDesc('evento_id')
            ->simplePaginate(12);
        $notas = AvaliacaoOng::where('pessoa_id', $pessoa->getKey())
            ->whereIn('evento_id', $eventos->getCollection()->modelKeys())
            ->pluck('nota', 'evento_id');

        $eventos->through(function (Evento $evento) use ($pessoa, $notas, $avaliacoes): array {
            $nota = $notas[$evento->getKey()] ?? null;
            $participou = $evento->agendas->contains(fn ($agenda) => $agenda->status_ativo === 'finalizado'
                && $agenda->inscricoes->contains(fn ($inscricao) => $inscricao->status_inscricao === 'participou'
                    && $inscricao->presenca && $inscricao->presenca_confirmada_em !== null));
            $elegivel = $nota === null && $pessoa->isApprovedVolunteer() && $evento->ong_id !== null
                && $evento->agendas_abertas_count === 0 && $participou && $avaliacoes->aceitaAvaliacaoOng($evento);

            return [
                'titulo' => $evento->nm_evento,
                'nota' => $nota,
                'avaliacao_url' => $elegivel ? route('identity.participation.rate', $evento) : null,
                'avaliacao_ate' => $elegivel ? $avaliacoes->prazoAvaliacaoOng($evento)?->toISOString() : null,
                'agendas' => $evento->agendas->map(fn ($agenda) => [
                    'inicio' => $agenda->data_inicio->toISOString(),
                    'situacao' => $agenda->status_ativo === 'cancelado' ? 'Cancelada' : match ($agenda->inscricoes->first()->status_inscricao) {
                        'participou' => 'Presença confirmada',
                        'não participou' => 'Ausência registrada',
                        'desistente' => 'Desistência registrada',
                        default => 'Cancelada',
                    },
                ]),
            ];
        });

        return ApiResponse::success($eventos);
    }

    public function rate(Request $request, Evento $evento, AvaliacaoService $avaliacoes)
    {
        $validated = $request->validate(['nota' => ['required', 'integer', 'between:1,5']]);
        $nota = $avaliacoes->registrarOng($evento, $request->attributes->get('web_account'), (int) $validated['nota']);

        return ApiResponse::success(['nota' => $nota->nota], 'Avaliação enviada.', 201);
    }
}
