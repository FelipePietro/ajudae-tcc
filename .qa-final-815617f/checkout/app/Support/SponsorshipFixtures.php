<?php

namespace App\Support;

/** Closed set of local presentation scenarios. */
final class SponsorshipFixtures
{
    public const APPLICANT = 'pessoa-indicacao';

    public const APPLICANT_EXAMPLES = [
        'pendente' => 'Pedido pendente',
        'ativo' => 'Vínculo ativo',
        'recusado' => 'Indicação recusada',
        'correcao' => 'Correção solicitada',
        'sem-indicacao' => 'Sem indicação',
    ];

    public static function profile(mixed $scenario): array
    {
        return match ($scenario) {
            'alternativa' => ['alternativa', 'Rui de Exemplo'],
            'insuficiente', 'lotado', 'bloqueado', 'semdados' => [$scenario, 'Pessoa de Exemplo'],
            default => ['principal', 'Marina de Exemplo'],
        };
    }
}
