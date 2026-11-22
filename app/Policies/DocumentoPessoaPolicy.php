<?php

namespace App\Policies;

use App\Models\DocumentoPessoa;
use App\Models\Ong;
use App\Models\Pessoa;

class DocumentoPessoaPolicy
{
    public function download(Pessoa|Ong $actor, DocumentoPessoa $document): bool
    {
        return app(PessoaPolicy::class)->viewPrivateHistory($actor, $document->pessoa);
    }
}
