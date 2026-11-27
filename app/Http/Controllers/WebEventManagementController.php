<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventoResource;
use App\Http\Responses\ManagedImageResponse;
use App\Models\Agenda;
use App\Models\CatEvento;
use App\Models\Causa;
use App\Models\Evento;
use App\Models\Habilidade;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\Recurso;
use App\Services\AgendaManagementService;
use App\Services\EventoManagementService;
use App\Services\ManagedImageStorage;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class WebEventManagementController extends Controller
{
    public function index(Request $request, EventoManagementService $service)
    {
        $porPagina = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $eventos = $service->listar($this->ator($request), (int) ($porPagina['per_page'] ?? 20))
            ->withQueryString()->through(fn (Evento $evento) => new EventoResource($evento));

        return ApiResponse::success($eventos);
    }

    public function catalogos(Request $request)
    {
        Gate::forUser($this->ator($request))->authorize('viewManagedAny', Evento::class);

        return ApiResponse::success([
            'categorias' => CatEvento::orderBy('nome_categoria')->get(['cat_evento_id', 'nome_categoria']),
            'habilidades' => Habilidade::orderBy('nome_habilidade')->get(['habilidade_id', 'nome_habilidade']),
            'causas' => Causa::orderBy('nome_causa')->get(['causa_id', 'nome_causa']),
            'recursos' => Recurso::orderBy('nome_recurso')->get(['recurso_id', 'nome_recurso']),
        ]);
    }

    public function show(Request $request, Evento $evento, EventoManagementService $service)
    {
        return ApiResponse::success(new EventoResource($service->consultar($this->ator($request), $evento)));
    }

    public function store(Request $request, EventoManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('create', Evento::class);
        $dados = $request->validate(EventoManagementService::regrasCriacao(false, $request->input('modalidade_evento') === 'online'));
        $chave = $request->header('Idempotency-Key');
        if ($chave !== null && ! preg_match('/\A[A-Za-z0-9._:-]{1,128}\z/', $chave)) {
            throw ValidationException::withMessages(['idempotency_key' => 'A chave de criação é inválida.']);
        }
        $evento = $service->criar($ator, $dados, $dados['imagem_evento'] ?? null, $chave);

        return ApiResponse::success(new EventoResource($evento), 'Evento criado com sucesso.', 201);
    }

    public function update(Request $request, Evento $evento, EventoManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('manage', $evento);
        $online = $request->input('modalidade_evento', $evento->modalidade_evento) === 'online';
        $dados = $request->validate(EventoManagementService::regrasEdicao($online, false));
        $evento = $service->atualizar($ator, $evento, $dados);

        return ApiResponse::success(new EventoResource($evento->load(['categoria', 'agendas', 'habilidades', 'causas', 'recursos'])), 'Evento atualizado com sucesso.');
    }

    public function cancelar(Request $request, Evento $evento, EventoManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('manage', $evento);
        $dados = $request->validate(EventoManagementService::regrasCancelamento());

        return ApiResponse::success(new EventoResource($service->cancelar($ator, $evento, $dados['motivo'])), 'Evento cancelado.');
    }

    public function storeAgenda(Request $request, Evento $evento, AgendaManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('createAgenda', $evento);
        $dados = $request->validate(AgendaManagementService::regras(true));

        return ApiResponse::success($service->criar($ator, $evento, $dados), 'Agenda criada com sucesso.', 201);
    }

    public function updateAgenda(Request $request, Agenda $agenda, AgendaManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('update', $agenda);
        $dados = $request->validate(AgendaManagementService::regras(false));

        return ApiResponse::success($service->atualizar($ator, $agenda, $dados), 'Agenda atualizada com sucesso.');
    }

    public function destroyAgenda(Request $request, Agenda $agenda, AgendaManagementService $service)
    {
        $service->excluir($this->ator($request), $agenda);

        return response()->noContent();
    }

    public function cancelarAgenda(Request $request, Agenda $agenda, AgendaManagementService $service)
    {
        $ator = $this->ator($request);
        Gate::forUser($ator)->authorize('manage', $agenda);
        $dados = $request->validate(EventoManagementService::regrasCancelamento());

        return ApiResponse::success($service->cancelar($ator, $agenda, $dados['motivo']), 'Agenda cancelada.');
    }

    public function showImage(Request $request, Evento $evento, ManagedImageStorage $images, ManagedImageResponse $response)
    {
        Gate::forUser($this->ator($request))->authorize('manage', $evento);

        return $response->serve($request, $evento, $images, private: true);
    }

    public function uploadImage(Request $request, Evento $evento, EventoManagementService $service)
    {
        $ator = $this->ator($request);
        $service->autorizarEdicao($ator, $evento);
        $dados = $request->validate(['imagem' => ['required', 'file', 'max:5120']]);
        $houveImagem = $evento->managed_image_path !== null;
        $evento = $service->salvarImagem($ator, $evento, $dados['imagem']);

        return ApiResponse::success($this->imagem($evento), 'Imagem salva.', $houveImagem ? 200 : 201);
    }

    public function removeImage(Request $request, Evento $evento, EventoManagementService $service)
    {
        $evento = $service->removerImagem($this->ator($request), $evento);

        return ApiResponse::success($this->imagem($evento), 'Imagem removida.');
    }

    private function ator(Request $request): Pessoa|Ong
    {
        return $request->attributes->get('web_account');
    }

    private function imagem(Evento $evento): array
    {
        return [
            'url' => $evento->managed_image_path ? route('identity.events.image.show', $evento, absolute: false) : null,
            'mime_type' => $evento->managed_image_mime,
            'size_bytes' => $evento->managed_image_size,
        ];
    }
}
