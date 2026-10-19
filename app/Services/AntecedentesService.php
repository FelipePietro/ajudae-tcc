<?php

namespace App\Services;

use App\Models\DocumentoPessoa;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;

class AntecedentesService
{
    public function situacao(Pessoa $pessoa): array
    {
        $this->alertarSeVencido($pessoa);

        $ultimaAprovacao = $this->ultimaAprovacao($pessoa);
        $versaoAtual = $pessoa->documentos()
            ->where('tipo', 'antecedentes_criminais')->where('ativo', true)
            ->latest('documento_pessoa_id')->first();
        $vencido = $ultimaAprovacao?->expires_at?->lessThanOrEqualTo(now()) ?? false;

        return [
            'status_validacao' => $pessoa->status_validacao,
            'ultima_aprovacao' => $ultimaAprovacao ? [
                'documento_pessoa_id' => $ultimaAprovacao->documento_pessoa_id,
                'status' => $vencido ? 'expirado' : 'aprovado',
                'validado_em' => $ultimaAprovacao->validated_at?->toISOString(),
                'valido_ate' => $ultimaAprovacao->expires_at?->toISOString(),
                'vigente' => ! $vencido && $ultimaAprovacao->expires_at !== null,
            ] : null,
            'renovacao' => $versaoAtual && $versaoAtual->getKey() !== $ultimaAprovacao?->getKey() ? [
                'documento_pessoa_id' => $versaoAtual->documento_pessoa_id,
                'status' => $versaoAtual->status,
                'enviado_em' => $versaoAtual->uploaded_at?->toISOString(),
            ] : null,
            'renovacao_necessaria' => $vencido,
        ];
    }

    public function alertarSeVencido(Pessoa $pessoa): bool
    {
        $ultima = $this->ultimaAprovacao($pessoa);
        if (! $ultima?->expires_at || $ultima->expires_at->greaterThan(now())) {
            return false;
        }

        return DB::transaction(function () use ($pessoa, $ultima) {
            $documento = DocumentoPessoa::whereKey($ultima->getKey())->lockForUpdate()->firstOrFail();
            if ($documento->alerta_renovacao_em !== null) {
                return false;
            }
            // Revalida sob lock para não avisar uma versão antiga após uma aprovação concorrente.
            if ($this->ultimaAprovacao($pessoa)?->getKey() !== $documento->getKey()) {
                return false;
            }
            $pessoa->notificacoes()->create([
                'tipo_notificacao' => 'antecedentes.renovacao_necessaria',
                'mensagem_notificacao' => 'Seu comprovante de antecedentes venceu. Envie uma renovação para análise.',
            ]);
            $documento->forceFill(['alerta_renovacao_em' => now()])->save();

            return true;
        });
    }

    private function ultimaAprovacao(Pessoa $pessoa): ?DocumentoPessoa
    {
        return $pessoa->documentos()->where('tipo', 'antecedentes_criminais')
            ->where('status', 'aprovado')
            ->orderByDesc('validated_at')->orderByDesc('documento_pessoa_id')->first();
    }
}
