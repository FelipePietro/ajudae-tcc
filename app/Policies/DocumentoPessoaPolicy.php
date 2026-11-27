<?php

namespace App\Policies;

use App\Models\DocumentoPessoa;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;

class DocumentoPessoaPolicy
{
    public function download(Pessoa|Ong $actor, DocumentoPessoa $document): bool
    {
        return app(PessoaPolicy::class)->viewPrivateHistory($actor, $document->pessoa);
    }

    public function downloadWeb(Pessoa|Ong $actor, DocumentoPessoa $document): bool
    {
        return $this->download($actor, $document)
            || ($actor instanceof Pessoa
                && AccountAccess::canOperate($actor)
                && $actor->hasPermission('usuarios.validar')
                && $document->isPendingBackgroundRenewal()
                && $document->hasRetainedFile());
    }
}
