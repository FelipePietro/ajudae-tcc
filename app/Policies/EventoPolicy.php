<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;

class EventoPolicy
{
    public function manage(Pessoa|Ong $user, Evento $evento): bool
    {
        if ($user instanceof Ong) {
            return $evento->ong_id !== null && $evento->ong_id === $user->ong_id;
        }

        if ($user->role_pessoa !== 'organizador' || $user->status_validacao !== 'aprovado') {
            return false;
        }

        if ($evento->pessoa_id === $user->pessoa_id) {
            return true;
        }

        $vinculo = $user->vinculoOngAtivo()->first();

        if ($vinculo) {
            return $evento->ong_id === $vinculo->ong_id
                && $evento->responsavel_pessoa_id === $user->pessoa_id;
        }

        return false;
    }

    public function update(Pessoa|Ong $user, Evento $evento): bool
    {
        return $this->manage($user, $evento);
    }

    public function delete(Pessoa|Ong $user, Evento $evento): bool
    {
        return $this->manage($user, $evento);
    }
}
