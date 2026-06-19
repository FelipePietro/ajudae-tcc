<?php

namespace App\Services;

use App\Models\Assinatura;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class AssinaturaService
{
    private const DOCUMENTO_PATH = 'termos/termos-v1.txt';

    public function registrar(Request $request, string $tipo, int $idEntidade): Assinatura
    {
        $this->validarEntidade($tipo, $idEntidade);

        $documentoUrl = $this->resolverDocumentoUrl();
        $documentoHash = $this->gerarDocumentoHash();

        return DB::transaction(function () use ($request, $tipo, $idEntidade, $documentoUrl, $documentoHash) {
            $assinatura = Assinatura::create([
                'dispositivo' => $this->resolverDispositivo($request),
                'ip_assinatura' => $request->ip(),
                'user_agent_assinatura' => substr($request->userAgent() ?? '', 0, 255),
                'documento_url' => $documentoUrl,
                'documento_hash' => $documentoHash,
                'geoloc_assinatura' => substr($request->input('geoloc_assinatura', ''), 0, 100),
            ]);

            if ($tipo === 'pessoa') {
                $assinatura->pessoas()->attach($idEntidade);
            } else {
                $assinatura->ongs()->attach($idEntidade);
            }

            return $assinatura->fresh(['pessoas', 'ongs']);
        });
    }

    private function validarEntidade(string $tipo, int $idEntidade): void
    {
        if ($tipo === 'pessoa') {
            Pessoa::query()->findOrFail($idEntidade);

            return;
        }

        if ($tipo === 'ong') {
            Ong::query()->findOrFail($idEntidade);

            return;
        }

        throw new InvalidArgumentException('Tipo de entidade invalido.');
    }

    private function resolverDispositivo(Request $request): string
    {
        if ($request->filled('dispositivo')) {
            return substr($request->input('dispositivo'), 0, 100);
        }

        return substr($request->userAgent() ?? 'desconhecido', 0, 100);
    }

    private function resolverDocumentoUrl(): string
    {
        return Storage::disk('public')->url(self::DOCUMENTO_PATH);
    }

    private function gerarDocumentoHash(): string
    {
        if (! Storage::disk('public')->exists(self::DOCUMENTO_PATH)) {
            throw new RuntimeException('Documento de termos nao encontrado.');
        }

        $conteudo = Storage::disk('public')->get(self::DOCUMENTO_PATH);

        return hash('sha256', $conteudo);
    }
}
