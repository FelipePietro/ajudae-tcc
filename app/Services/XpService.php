<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Agenda;
use App\Models\CampanhaXp;
use App\Models\Evento;
use App\Models\Pessoa;
use App\Models\XpTransacao;
use Illuminate\Support\Facades\DB;

class XpService
{
    public const XP_BASE = 100;

    public function multiplicadorDuracao(Agenda $agenda): float
    {
        $horas = $agenda->data_inicio->diffInSeconds($agenda->data_fim, false) / 3600;

        return $horas <= 1 ? $horas : 1 + (($horas - 1) * 0.10);
    }

    public function concederAgenda(Agenda $agenda): int
    {
        return DB::transaction(function () use ($agenda) {
            $evento = Evento::whereKey($agenda->evento_id)->lockForUpdate()->firstOrFail();
            $agenda = Agenda::whereKey($agenda->getKey())->lockForUpdate()->firstOrFail();
            if ($agenda->status_ativo !== 'finalizado'
                || now()->lessThan($agenda->data_fim)
                || ! in_array($evento?->status_evento, ['ativo', 'finalizado'], true)) {
                throw new ValidationConflictException('A Agenda ainda não está apta à concessão de XP.');
            }

            $duracao = $this->multiplicadorDuracao($agenda);
            if ($duracao < 0.5) {
                throw new ValidationConflictException('A duração declarada da Agenda é inválida para XP.');
            }
            $campanhas = CampanhaXp::query()
                ->where('cat_evento_id', $evento->cat_evento_id)
                ->where('ativa', true)
                ->where('inicio', '<=', $agenda->data_inicio)
                ->where('fim', '>=', $agenda->data_inicio)
                ->orderBy('campanha_xp_id')
                ->get();
            $produto = $campanhas->reduce(fn (float $valor, CampanhaXp $campanha) => $valor * (float) $campanha->multiplicador, 1.0);
            $tematico = min($produto, 3.0);
            $xp = (int) round(self::XP_BASE * $duracao * $tematico);
            $fatores = $campanhas->map(fn (CampanhaXp $campanha) => [
                'campanha_xp_id' => $campanha->campanha_xp_id,
                'nome' => $campanha->nome,
                'multiplicador' => $campanha->multiplicador,
                'cat_evento_id' => $campanha->cat_evento_id,
                'inicio' => $campanha->inicio->toISOString(),
                'fim' => $campanha->fim->toISOString(),
            ])->all();

            $concedidas = 0;
            $inscricoes = $agenda->inscricoes()
                ->where('status_inscricao', 'participou')
                ->where('presenca', true)
                ->whereNotNull('presenca_confirmada_em')
                ->orderBy('pessoa_id')
                ->orderBy('inscricao_id')
                ->get();
            foreach ($inscricoes as $inscricao) {
                $pessoa = Pessoa::whereKey($inscricao->pessoa_id)->lockForUpdate()->firstOrFail();
                if (XpTransacao::where('inscricao_id', $inscricao->inscricao_id)->exists()) {
                    continue;
                }

                $transacao = new XpTransacao;
                $transacao->forceFill([
                    'pessoa_id' => $pessoa->pessoa_id,
                    'agenda_id' => $agenda->agenda_id,
                    'inscricao_id' => $inscricao->inscricao_id,
                    'evento_id' => $evento->evento_id,
                    'xp_base' => self::XP_BASE,
                    'multiplicador_duracao' => $duracao,
                    'multiplicador_tematico' => $tematico,
                    'xp_final' => $xp,
                    'campanhas_aplicadas' => $fatores,
                    'concedido_em' => now(),
                ])->save();
                $pessoa->forceFill(['xp_pessoa' => $pessoa->xp_pessoa + $xp])->save();
                $concedidas++;
            }

            return $concedidas;
        });
    }
}
