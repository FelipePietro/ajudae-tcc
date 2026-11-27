<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Services\EventoModerationService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

final class WebEventModerationController extends Controller
{
    public function index(Request $request)
    {
        $this->revisor($request);
        $dados = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $termo = strtr($dados['q'] ?? '', ['!' => '!!', '%' => '!%', '_' => '!_']);
        $eventos = Evento::query()
            ->where('status_evento', EventoModerationService::AGUARDANDO)
            ->when($termo !== '', fn ($query) => $query->whereRaw("nm_evento LIKE ? ESCAPE '!'", ['%'.$termo.'%']))
            ->with(['agendas' => fn ($query) => $query->orderBy('data_inicio')->orderBy('agenda_id')])
            ->orderBy('created_at')
            ->orderBy('evento_id')
            ->simplePaginate(12)
            ->appends($dados)
            ->through(fn (Evento $evento) => [
                'titulo' => $evento->nm_evento,
                'descricao' => $evento->descricao_evento,
                'cidade' => $evento->cidade_evento,
                'uf' => $evento->uf_evento,
                'modalidade' => $evento->modalidade_evento,
                'decisao_url' => route('identity.admin.events.decide', $evento),
                'agendas' => $evento->agendas->map(fn (Agenda $agenda) => [
                    'inicio' => $agenda->data_inicio->toISOString(),
                    'fim' => $agenda->data_fim->toISOString(),
                    'vagas' => $agenda->vagas_agenda,
                    'status' => $agenda->status_ativo,
                ]),
            ]);

        return ApiResponse::success($eventos);
    }

    public function decide(Request $request, int $evento, EventoModerationService $moderacao)
    {
        $admin = $this->revisor($request);
        $dados = $request->validate(EventoModerationService::regras(true));
        $registro = $moderacao->decidir($admin, $evento, $dados['status_evento'], $dados['motivo'] ?? null);

        return ApiResponse::success(
            ['status_evento' => $registro->status_evento],
            $registro->status_evento === 'ativo' ? 'Publicação aprovada.' : 'Publicação reprovada.',
        );
    }

    private function revisor(Request $request): Pessoa
    {
        $ator = $request->attributes->get('web_account');
        abort_unless($ator instanceof Pessoa && AccountAccess::canOperate($ator) && $ator->hasPermission('eventos.aprovar'), 403);

        return $ator;
    }
}
