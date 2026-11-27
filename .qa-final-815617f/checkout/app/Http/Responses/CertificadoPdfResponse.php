<?php

namespace App\Http\Responses;

use App\Models\Certificado;
use App\Support\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CertificadoPdfResponse
{
    public function download(Certificado $certificado): Response
    {
        try {
            $bytes = Pdf::loadView('certificados.documento', ['certificado' => $certificado])
                ->setPaper('a4')->output();
        } catch (Throwable) {
            return ApiResponse::error('Não foi possível gerar o certificado agora.', null, 503);
        }

        $codigo = preg_replace('/[^A-Za-z0-9-]/', '', $certificado->codigo_verificacao);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificado-ajudae-'.$codigo.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
