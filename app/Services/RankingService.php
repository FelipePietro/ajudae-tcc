<?php

namespace App\Services;

use App\Models\Pessoa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RankingService
{
    public function listar(?string $cidade, int $porPagina): LengthAwarePaginator
    {
        $pessoas = Pessoa::query()
            ->select(['pessoa_id', 'nm_pessoa', 'pfp_pessoa_link', 'xp_pessoa'])
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')
            ->when($cidade !== null, fn ($query) => $query->where('cidade_pessoa', $cidade))
            ->orderByDesc('xp_pessoa')
            ->orderBy('pessoa_id')
            ->paginate($porPagina);

        $posicao = ($pessoas->currentPage() - 1) * $pessoas->perPage();

        return $pessoas->through(function (Pessoa $pessoa) use (&$posicao) {
            return [
                'posicao' => ++$posicao,
                'xp' => $pessoa->xp_pessoa,
                'pessoa' => [
                    'pessoa_id' => $pessoa->pessoa_id,
                    'nm_pessoa' => $pessoa->nm_pessoa,
                    'pfp_pessoa_link' => $pessoa->pfp_pessoa_link,
                ],
            ];
        });
    }
}
