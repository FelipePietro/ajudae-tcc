<?php

namespace App\Policies;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;

class PessoaPolicy
{
    public function viewPrivateHistory(Pessoa|Ong $actor, Pessoa $subject): bool
    {
        return $actor instanceof Pessoa && AccountAccess::canOperate($actor)
            && ($actor->is($subject)
                ? AccountAccess::hasPersonalJourney($actor)
                : $this->reviewAssignedCase($actor, $subject));
    }

    public function reviewAssignedCase(Pessoa $actor, Pessoa $subject): bool
    {
        return AccountAccess::canOperate($actor)
            && $actor->hasPermission('usuarios.validar')
            && ! $subject->exclusao_pendente
            && $subject->status_validacao === 'em_analise'
            && (int) $subject->admin_analisando_id === (int) $actor->getKey()
            && $subject->analise_assumida_em?->gt(now()->subHours(24));
    }
}
