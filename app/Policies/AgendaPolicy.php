<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\Ong;
use App\Models\Pessoa;

class AgendaPolicy
{
    public function manage(Pessoa|Ong $user, Agenda $agenda): bool
    {
        return app(EventoPolicy::class)->manage($user, $agenda->evento);
    }

    public function update(Pessoa|Ong $user, Agenda $agenda): bool
    {
        return $this->manage($user, $agenda);
    }

    public function delete(Pessoa|Ong $user, Agenda $agenda): bool
    {
        return $this->manage($user, $agenda);
    }
}
