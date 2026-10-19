<?php

namespace App\Services;

use App\Models\Inscricao;
use App\Models\Pessoa;
use Illuminate\Support\Collection;

class ConfiabilidadeService
{
    public const PARTICIPACOES_PARA_SCORE = 3;

    public function resumo(Pessoa $pessoa): array
    {
        [$ocorrencias, $participacoes] = $this->ocorrencias($pessoa);

        return $this->resumir($ocorrencias, $participacoes);
    }

    public function resumoPublico(Pessoa $pessoa): array
    {
        return $this->publicar($this->resumo($pessoa));
    }

    public function resumosPublicosPorPessoas(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        $inscricoes = Inscricao::query()
            ->whereIn('pessoa_id', $ids->unique()->values())
            ->whereHas('agenda', fn ($query) => $query->where('status_ativo', '!=', 'cancelado'))
            ->get()
            ->groupBy('pessoa_id');

        $resumos = [];
        foreach ($ids->unique() as $id) {
            [$ocorrencias, $participacoes] = $this->ocorrenciasDasInscricoes($inscricoes->get($id, collect()));
            $resumos[$id] = $this->publicar($this->resumir($ocorrencias, $participacoes));
        }

        return $resumos;
    }

    public function detalhe(Pessoa $pessoa): array
    {
        [$ocorrencias, $participacoes] = $this->ocorrencias($pessoa);
        $contagens = [];
        foreach ($ocorrencias as $ocorrencia) {
            $tipo = $ocorrencia['tipo'];
            $contagens[$tipo] = ($contagens[$tipo] ?? 0) + 1;
        }

        return array_merge($this->resumir($ocorrencias, $participacoes), [
            'ocorrencias' => $ocorrencias,
            'contagens_por_tipo' => $contagens,
        ]);
    }

    private function ocorrencias(Pessoa $pessoa): array
    {
        $inscricoes = Inscricao::query()
            ->where('pessoa_id', $pessoa->pessoa_id)
            ->whereHas('agenda', fn ($query) => $query->where('status_ativo', '!=', 'cancelado'))
            ->get();

        return $this->ocorrenciasDasInscricoes($inscricoes);
    }

    private function ocorrenciasDasInscricoes(Collection $inscricoes): array
    {
        $ocorrencias = [];
        $participacoes = 0;
        foreach ($inscricoes as $inscricao) {
            $tipo = null;
            $delta = null;
            $quando = null;

            if ($inscricao->status_inscricao === 'participou'
                && $inscricao->presenca === true
                && $inscricao->presenca_confirmada_em !== null) {
                $tipo = 'participacao';
                $delta = 3;
                $quando = $inscricao->presenca_confirmada_em;
                $participacoes++;
            } elseif ($inscricao->status_inscricao === 'não participou'
                && $inscricao->presenca === false
                && $inscricao->presenca_confirmada_em !== null) {
                $tipo = 'ausencia';
                $delta = -20;
                $quando = $inscricao->presenca_confirmada_em;
            } elseif ($inscricao->status_inscricao === 'desistente' && $inscricao->desistiu_em !== null) {
                $tipo = $inscricao->desistencia_sem_penalidade
                    ? 'desistencia_sem_penalidade'
                    : ($inscricao->desistencia_tardia ? 'desistencia_tardia' : 'desistencia_normal');
                $delta = match ($tipo) {
                    'desistencia_sem_penalidade' => 0,
                    'desistencia_tardia' => -10,
                    default => -5,
                };
                $quando = $inscricao->desistiu_em;
            }

            if ($tipo !== null) {
                $ocorrencias[] = [
                    'inscricao_id' => $inscricao->inscricao_id,
                    'tipo' => $tipo,
                    'delta' => $delta,
                    'registrado_em' => $quando->toISOString(),
                    '_quando' => $quando,
                ];
            }
        }

        usort($ocorrencias, fn ($a, $b) => $a['_quando']->getTimestamp() <=> $b['_quando']->getTimestamp()
            ?: $a['inscricao_id'] <=> $b['inscricao_id']);
        foreach ($ocorrencias as &$ocorrencia) {
            unset($ocorrencia['_quando']);
        }

        return [$ocorrencias, $participacoes];
    }

    private function publicar(array $resumo): array
    {
        return $resumo['formada']
            ? ['formada' => true, 'score' => $resumo['score']]
            : $resumo;
    }

    private function resumir(array $ocorrencias, int $participacoes): array
    {
        $formada = $participacoes >= self::PARTICIPACOES_PARA_SCORE;
        $score = 100;
        foreach ($ocorrencias as $ocorrencia) {
            $score = max(0, min(100, $score + $ocorrencia['delta']));
        }

        return [
            'formada' => $formada,
            'score' => $formada ? $score : null,
            'participacoes_confirmadas' => $participacoes,
            'necessarias' => self::PARTICIPACOES_PARA_SCORE,
            'estado' => $formada ? 'formada' : "Em formação — {$participacoes} de 3",
        ];
    }
}
