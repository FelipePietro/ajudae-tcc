<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;

class EventoPolicy
{
    public function manage(Pessoa|Ong $user, Evento $evento, bool $lock = false): bool
    {
        if ($lock) {
            $current = $user->newQuery()->whereKey($user->getKey())->lockForUpdate()->first();
            if (! $current || (int) $current->auth_version !== (int) $user->auth_version) {
                return false;
            }
            $user = $current;
        }

        if (! AccountAccess::canOperate($user)) {
            return false;
        }

        if ($user instanceof Ong) {
            return $evento->ong_id === $user->ong_id && AccountAccess::activeOng($user);
        }

        if ($user->role_pessoa !== 'organizador' || $user->status_validacao !== 'aprovado') {
            return false;
        }

        $links = $user->vinculoOngAtivo()->when($lock, fn ($query) => $query->lockForUpdate());
        if ($evento->ong_id === null) {
            return $evento->pessoa_id === $user->pessoa_id
                && ($evento->responsavel_pessoa_id === null || $evento->responsavel_pessoa_id === $user->pessoa_id)
                && $links->first() === null;
        }

        if ($evento->responsavel_pessoa_id !== $user->pessoa_id) {
            return false;
        }

        $ong = Ong::whereKey($evento->ong_id)->when($lock, fn ($query) => $query->lockForUpdate())->first();

        return $ong !== null && AccountAccess::activeOng($ong) && $links->first()?->ong_id === $ong->ong_id;
    }

    public function operate(Pessoa|Ong $user, Evento $evento, bool $lock = false): bool
    {
        return (! $user instanceof Ong || $evento->responsavel_pessoa_id === null)
            && $this->manage($user, $evento, $lock);
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
