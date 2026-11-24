<?php

namespace App\Services;

use App\Models\Pessoa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public function listar(?string $cidade, int $porPagina): LengthAwarePaginator
    {
        $pessoas = $this->elegiveis()
            ->select(['pessoa_id', 'nm_pessoa', 'pfp_pessoa_link', 'xp_pessoa'])
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

    public function web(?string $cidade, ?string $busca): array
    {
        $cidades = $this->elegiveis()->whereNotNull('cidade_pessoa')->where('cidade_pessoa', '<>', '')
            ->distinct()->orderBy('cidade_pessoa')->pluck('cidade_pessoa');
        $ranking = $this->elegiveis()->select('nm_pessoa', 'cidade_pessoa', 'xp_pessoa')
            ->when($cidade !== null, fn (Builder $query) => $query->where('cidade_pessoa', $cidade))
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY xp_pessoa DESC, pessoa_id ASC) AS posicao');
        $classificacao = DB::query()->fromSub($ranking, 'ranking')->orderBy('posicao');
        $podio = (clone $classificacao)->limit(3)->get();

        if ($busca !== null) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $busca).'%';
            $classificacao->whereRaw("nm_pessoa LIKE ? ESCAPE '!'", [$pattern]);
        }

        return [
            'cidades' => $cidades,
            'podio' => $podio,
            'ranking' => $classificacao->paginate(20)->withQueryString(),
        ];
    }

    private function elegiveis(): Builder
    {
        return Pessoa::query()->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aprovado')->where('exclusao_pendente', false);
    }
}
