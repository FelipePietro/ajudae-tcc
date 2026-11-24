<?php

namespace App\Services;

use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PessoaRegistrationService
{
    public function __construct(private readonly PadrinhoService $padrinhos) {}

    public function register(array $validated, ?string $ip, ?string $userAgent): Pessoa
    {
        $codigo = $validated['codigo_padrinho'] ?? null;
        unset($validated['aceitou_termos'], $validated['codigo_padrinho']);
        $validated['senha_pessoa'] = Hash::make($validated['senha_pessoa']);

        return DB::transaction(function () use ($validated, $ip, $userAgent, $codigo) {
            $pessoa = Pessoa::create($validated)->refresh();
            $pessoa->assinaturas()->create([
                'tipo_termo' => 'termos_de_uso',
                'versao_termo' => '1.0',
                'aceito_em' => now(),
                'ip_assinatura' => $ip,
                'user_agent_assinatura' => mb_substr((string) $userAgent, 0, 255),
                'documento_hash' => hash('sha256', 'termos_de_uso:1.0'),
            ]);

            if ($codigo !== null) {
                $this->padrinhos->indicar($pessoa, $codigo, cadastro: true);
            }

            return $pessoa;
        }, 3);
    }
}
