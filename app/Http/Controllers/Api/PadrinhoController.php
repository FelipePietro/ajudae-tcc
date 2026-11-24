<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acompanhamento;
use App\Models\Pessoa;
use App\Models\SolicitacaoPadrinho;
use App\Services\PadrinhoService;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class PadrinhoController extends Controller
{
    public function elegiveis(Request $request, PadrinhoService $service)
    {
        $pessoa = $this->pessoa($request);
        if (! $pessoa->isApprovedVolunteer()) {
            throw new AuthorizationException('Somente voluntários aprovados podem consultar padrinhos.');
        }
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return ApiResponse::success($service->candidatos()
            ->whereKeyNot($pessoa->pessoa_id)
            ->orderBy('pessoa_id')->paginate($validated['per_page'] ?? 20));
    }

    public function meus(Request $request)
    {
        $pessoa = $this->pessoa($request);
        $ids = [$pessoa->pessoa_id];

        return ApiResponse::success([
            'solicitacoes' => SolicitacaoPadrinho::query()
                ->where('padrinho_id', $ids[0])->orWhere('apadrinhado_id', $ids[0])
                ->orderByDesc('solicitada_em')->paginate(20)
                ->through(fn ($pedido) => $this->pedidoPublico($pedido)),
            'vinculos' => Acompanhamento::query()
                ->where('padrinho_id', $ids[0])->orWhere('apadrinhado_id', $ids[0])
                ->orderByDesc('created_at')->paginate(20)
                ->through(fn ($vinculo) => $this->vinculoPublico($vinculo)),
        ]);
    }

    public function codigo(Request $request, PadrinhoService $service)
    {
        $indicacao = $service->codigo($this->pessoa($request));

        return ApiResponse::success(['codigo' => $indicacao?->codigo, 'emitido_em' => $indicacao?->emitido_em?->toISOString()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function emitirCodigo(Request $request, PadrinhoService $service)
    {
        $indicacao = $service->emitirCodigo($this->pessoa($request));

        return ApiResponse::success(['codigo' => $indicacao->codigo, 'emitido_em' => $indicacao->emitido_em->toISOString()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function revogarCodigo(Request $request, PadrinhoService $service)
    {
        $service->revogarCodigo($this->pessoa($request));

        return ApiResponse::success(null, 'Código revogado.');
    }

    public function indicar(Request $request, PadrinhoService $service)
    {
        $dados = $request->validate(['codigo_padrinho' => ['required', 'string', 'regex:/\A[0-9a-f]{64}\z/i']]);
        $pedido = $service->indicar($this->pessoa($request), $dados['codigo_padrinho']);

        return ApiResponse::success($this->pedidoPublico($pedido), 'Indicação registrada.');
    }

    public function recebidas(Request $request)
    {
        $pessoa = $this->pessoa($request);
        $validated = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);

        return ApiResponse::success(SolicitacaoPadrinho::query()
            ->where('padrinho_id', $pessoa->pessoa_id)->where('status', 'pendente')
            ->orderBy('solicitada_em')->paginate($validated['per_page'] ?? 20)
            ->through(fn ($pedido) => $this->pedidoPublico($pedido)));
    }

    public function solicitar(Request $request, PadrinhoService $service)
    {
        $validated = $request->validate(['padrinho_id' => ['required', 'integer', 'exists:pessoa,pessoa_id']]);
        $pedido = $service->solicitar($this->pessoa($request), $validated['padrinho_id']);

        return ApiResponse::success($this->pedidoPublico($pedido), 'Solicitação enviada.', 201);
    }

    public function aceitar(Request $request, SolicitacaoPadrinho $pedido, PadrinhoService $service)
    {
        $pedido = $service->decidirPedido($pedido, $this->pessoa($request), true);

        return ApiResponse::success($this->pedidoPublico($pedido), 'Solicitação aceita.');
    }

    public function recusar(Request $request, SolicitacaoPadrinho $pedido, PadrinhoService $service)
    {
        $pedido = $service->decidirPedido($pedido, $this->pessoa($request), false);

        return ApiResponse::success($this->pedidoPublico($pedido), 'Solicitação recusada.');
    }

    public function cancelar(Request $request, SolicitacaoPadrinho $pedido, PadrinhoService $service)
    {
        $pedido = $service->cancelarPedido($pedido, $this->pessoa($request));

        return ApiResponse::success($this->pedidoPublico($pedido), 'Solicitação cancelada.');
    }

    public function vinculo(Request $request, Acompanhamento $vinculo, PadrinhoService $service)
    {
        $pessoa = $this->pessoa($request);
        if (! in_array($pessoa->pessoa_id, [$vinculo->padrinho_id, $vinculo->apadrinhado_id], true)) {
            abort(404);
        }

        return ApiResponse::success($this->vinculoPublico($vinculo) + ['progresso' => $service->progresso($vinculo)]);
    }

    public function encerrar(Request $request, Acompanhamento $vinculo, PadrinhoService $service)
    {
        $validated = $request->validate(['motivo' => ['required', 'string', 'max:2000', 'not_regex:/^\s*$/']]);
        $vinculo = $service->encerrar($vinculo, $this->pessoa($request), $validated['motivo']);

        return ApiResponse::success($this->vinculoPublico($vinculo), 'Vínculo encerrado.');
    }

    public function solicitarConclusao(Request $request, Acompanhamento $vinculo, PadrinhoService $service)
    {
        $pedido = $service->solicitarConclusao($vinculo, $this->pessoa($request));

        return ApiResponse::success([
            'conclusao_padrinho_id' => $pedido->getKey(),
            'acompanhamento_id' => $pedido->acompanhamento_id,
            'status' => $pedido->status,
            'solicitada_em' => $pedido->solicitada_em?->toISOString(),
        ], 'Conclusão enviada para análise.', 201);
    }

    private function pessoa(Request $request): Pessoa
    {
        $pessoa = $request->user();
        if (! $pessoa instanceof Pessoa || $pessoa->role_pessoa !== 'voluntario') {
            throw new AuthorizationException;
        }

        return $pessoa;
    }

    private function pedidoPublico(SolicitacaoPadrinho $pedido): array
    {
        return [
            'solicitacao_padrinho_id' => $pedido->getKey(),
            'padrinho_id' => $pedido->padrinho_id,
            'apadrinhado_id' => $pedido->apadrinhado_id,
            'status' => $pedido->status,
            'solicitada_em' => $pedido->solicitada_em?->toISOString(),
            'decidida_em' => $pedido->decidida_em?->toISOString(),
            'acompanhamento_id' => $pedido->acompanhamento_id,
        ];
    }

    private function vinculoPublico(Acompanhamento $vinculo): array
    {
        return [
            'acompanhamento_id' => $vinculo->getKey(),
            'padrinho_id' => $vinculo->padrinho_id,
            'apadrinhado_id' => $vinculo->apadrinhado_id,
            'status' => $vinculo->status,
            'aceito_em' => $vinculo->aceito_em?->toISOString(),
            'encerrado_em' => $vinculo->encerrado_em?->toISOString(),
            'motivo_encerramento' => $vinculo->motivo_encerramento,
            'concluido_em' => $vinculo->concluido_em?->toISOString(),
        ];
    }
}
