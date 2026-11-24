<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificadoResource;
use App\Http\Responses\CertificadoPdfResponse;
use App\Models\Certificado;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Services\CertificadoService;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CertificadoController extends Controller
{
    public function emitir(Request $request, int $id, CertificadoService $service)
    {
        $pessoa = $request->user();
        if (! $pessoa instanceof Pessoa || ! AccountAccess::hasPersonalJourney($pessoa)) {
            throw new AuthorizationException;
        }

        $certificado = $service->emitir(Evento::findOrFail($id), $pessoa);

        return ApiResponse::success(new CertificadoResource($certificado), 'Certificado disponível.');
    }

    public function show(Request $request, int $id)
    {
        $pessoa = $request->user();
        if (! $pessoa instanceof Pessoa || ! AccountAccess::hasPersonalJourney($pessoa)) {
            throw new AuthorizationException;
        }

        $certificado = Certificado::where('evento_id', $id)
            ->where('pessoa_id', $pessoa->pessoa_id)->firstOrFail();

        return ApiResponse::success(new CertificadoResource($certificado));
    }

    public function verificar(string $codigo)
    {
        $certificado = Certificado::where('codigo_verificacao', $codigo)->firstOrFail();

        return ApiResponse::success([
            'valido' => true,
            'codigo_verificacao' => $certificado->codigo_verificacao,
            'nome_participante' => $certificado->nome_participante,
            'nome_evento' => $certificado->nome_evento,
            'nome_responsavel' => $certificado->nome_responsavel,
            'total_minutos' => $certificado->total_minutos,
            'horas' => round($certificado->total_minutos / 60, 4),
            'emitido_em' => $certificado->emitido_em->toISOString(),
        ]);
    }

    public function download(Request $request, Certificado $certificado, CertificadoPdfResponse $pdf)
    {
        $pessoa = $request->user();
        if (! $pessoa instanceof Pessoa || ! AccountAccess::hasPersonalJourney($pessoa) || $certificado->pessoa_id !== $pessoa->pessoa_id) {
            throw new AuthorizationException;
        }

        return $pdf->download($certificado);
    }
}
