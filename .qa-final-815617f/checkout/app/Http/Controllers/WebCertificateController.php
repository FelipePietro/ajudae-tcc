<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationConflictException;
use App\Http\Resources\CertificadoResource;
use App\Http\Responses\CertificadoPdfResponse;
use App\Models\AvaliacaoVoluntario;
use App\Models\Certificado;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Services\CertificadoService;
use App\Services\MonitoriaService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebCertificateController extends Controller
{
    public function index(Request $request, MonitoriaService $monitoria): JsonResponse
    {
        $pessoa = $this->pessoa($request);
        $proprios = Certificado::where('pessoa_id', $pessoa->getKey());
        $eventos = Evento::query()->select('evento_id', 'nm_evento', 'status_evento')
            ->where(fn (Builder $eventos) => $eventos
                ->whereIn('evento_id', (clone $proprios)->select('evento_id'))
                ->orWhereHas('agendas', fn (Builder $agendas) => $agendas
                    ->where('status_ativo', 'finalizado')
                    ->whereHas('inscricoes', fn (Builder $inscricoes) => $inscricoes
                        ->where('pessoa_id', $pessoa->getKey())
                        ->where('status_inscricao', 'participou')
                        ->where('presenca', true)
                        ->whereNotNull('presenca_confirmada_em'))))
            ->orderByDesc('evento_id')
            ->simplePaginate(12);

        $ids = $eventos->getCollection()->modelKeys();
        $emitidos = $proprios->whereIn('evento_id', $ids)->get()->keyBy('evento_id');
        $avaliados = AvaliacaoVoluntario::where('pessoa_id', $pessoa->getKey())
            ->whereIn('evento_id', $ids)->whereBetween('nota', [1, 5])->pluck('evento_id')->all();

        return ApiResponse::success($eventos->through(function (Evento $evento) use ($pessoa, $emitidos, $avaliados, $monitoria, $request): array {
            $emitido = $emitidos->get($evento->getKey());
            $situacao = match (true) {
                $emitido !== null => 'emitido',
                $evento->status_evento === 'cancelado' => 'cancelado',
                $evento->status_evento !== 'finalizado' => 'aguarda_evento',
                ! in_array($evento->getKey(), $avaliados) => 'aguarda_avaliacao',
                default => 'disponivel',
            };
            if ($situacao === 'disponivel') {
                try {
                    $monitoria->exigirValidacaoParaCertificado($evento, $pessoa);
                } catch (ValidationConflictException) {
                    $situacao = 'aguarda_monitoria';
                }
            }

            return [
                'evento' => $emitido?->nome_evento ?? $evento->nm_evento,
                'situacao' => $situacao,
                'emitir_url' => $situacao === 'disponivel' ? route('identity.certificates.issue', $evento) : null,
                'certificado' => $emitido === null ? null : $this->dados($request, $emitido),
            ];
        }));
    }

    public function issue(Request $request, Evento $evento, CertificadoService $service): JsonResponse
    {
        $certificado = $service->emitir($evento, $this->pessoa($request));

        return ApiResponse::success($this->dados($request, $certificado), 'Certificado disponível.');
    }

    public function download(Request $request, Certificado $certificado, CertificadoPdfResponse $pdf): Response
    {
        abort_unless((int) $certificado->pessoa_id === (int) $this->pessoa($request)->getKey(), 403);

        return $pdf->download($certificado);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:64', 'regex:/\A[A-Za-z0-9-]+\z/'],
        ]);
        $certificado = Certificado::query()
            ->select('codigo_verificacao', 'nome_evento', 'total_minutos', 'emitido_em')
            ->where('codigo_verificacao', strtoupper($validated['codigo']))->firstOrFail();

        return ApiResponse::success([
            'valido' => true,
            'codigo_verificacao' => $certificado->codigo_verificacao,
            'nome_evento' => $certificado->nome_evento,
            'total_minutos' => $certificado->total_minutos,
            'emitido_em' => $certificado->emitido_em->toISOString(),
        ]);
    }

    private function pessoa(Request $request): Pessoa
    {
        $pessoa = $request->attributes->get('web_account');
        abort_unless($pessoa instanceof Pessoa && AccountAccess::hasPersonalJourney($pessoa) && AccountAccess::canOperate($pessoa), 403);

        return $pessoa;
    }

    private function dados(Request $request, Certificado $certificado): array
    {
        return (new CertificadoResource($certificado))->resolve($request) + [
            'pdf_url' => route('identity.certificates.download', $certificado),
            'verificar_url' => route('certificados.verificar', ['codigo' => $certificado->codigo_verificacao]),
        ];
    }
}
