<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\AvaliacaoOng;
use App\Models\AvaliacaoVoluntario;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Policies\EventoPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AvaliacaoService
{
    public function mediaVoluntario(Pessoa $pessoa): ?float
    {
        $media = AvaliacaoVoluntario::where('pessoa_id', $pessoa->pessoa_id)->avg('nota');

        return $media === null ? null : (float) $media;
    }

    public function mediasPorPessoas(Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return AvaliacaoVoluntario::query()
            ->whereIn('pessoa_id', $ids->unique()->values())
            ->select('pessoa_id')
            ->selectRaw('AVG(nota) as media')
            ->groupBy('pessoa_id')
            ->get()
            ->mapWithKeys(fn ($avaliacao) => [$avaliacao->pessoa_id => (float) $avaliacao->media])
            ->all();
    }

    public function registrarVoluntario(Evento $evento, Pessoa $voluntario, Pessoa|Ong $ator, int $nota): AvaliacaoVoluntario
    {
        return DB::transaction(function () use ($evento, $voluntario, $ator, $nota) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            $this->autorizarResponsavel($evento, $ator);
            $this->garantirEventoConcluidoEParticipacao($evento, $voluntario);

            if (AvaliacaoVoluntario::where('evento_id', $evento->evento_id)
                ->where('pessoa_id', $voluntario->pessoa_id)->exists()) {
                throw new ValidationConflictException('Esta pessoa já foi avaliada neste evento.');
            }

            return AvaliacaoVoluntario::create([
                'evento_id' => $evento->evento_id,
                'pessoa_id' => $voluntario->pessoa_id,
                'nota' => $nota,
                'avaliador_tipo' => $ator instanceof Ong ? 'ong' : 'pessoa',
                'avaliador_id' => $ator->getKey(),
            ]);
        });
    }

    public function registrarOng(Evento $evento, Pessoa $voluntario, int $nota): AvaliacaoOng
    {
        return DB::transaction(function () use ($evento, $voluntario, $nota) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();
            if (! $voluntario->isApprovedVolunteer()) {
                throw new AuthorizationException('Somente o voluntário participante pode avaliar a ONG.');
            }
            if ($evento->ong_id === null) {
                throw new ValidationConflictException('Evento independente não possui ONG para avaliar.');
            }
            $this->garantirEventoConcluidoEParticipacao($evento, $voluntario);

            if (AvaliacaoOng::where('evento_id', $evento->evento_id)
                ->where('pessoa_id', $voluntario->pessoa_id)->exists()) {
                throw new ValidationConflictException('A ONG já foi avaliada por esta pessoa neste evento.');
            }

            return AvaliacaoOng::create([
                'evento_id' => $evento->evento_id,
                'pessoa_id' => $voluntario->pessoa_id,
                'nota' => $nota,
            ]);
        });
    }

    private function autorizarResponsavel(Evento $evento, Pessoa|Ong $ator): void
    {
        if ($evento->responsavel_pessoa_id !== null) {
            if (! ($ator instanceof Pessoa)
                || $ator->pessoa_id !== $evento->responsavel_pessoa_id
                || ! app(EventoPolicy::class)->manage($ator, $evento)) {
                throw new AuthorizationException('Somente o responsável operacional pode avaliar o voluntário.');
            }

            return;
        }

        if (! ($ator instanceof Ong) || $ator->ong_id !== $evento->ong_id) {
            throw new AuthorizationException('Somente a ONG proprietária pode avaliar o voluntário.');
        }
    }

    private function garantirEventoConcluidoEParticipacao(Evento $evento, Pessoa $voluntario): void
    {
        if ($evento->status_evento !== 'ativo') {
            throw new ValidationConflictException('Somente evento ativo recebe avaliações.');
        }
        if ($evento->agendas()->whereNotIn('status_ativo', ['finalizado', 'cancelado'])->exists()) {
            throw new ValidationConflictException('Todas as agendas precisam estar finalizadas ou canceladas.');
        }

        $participou = Inscricao::where('pessoa_id', $voluntario->pessoa_id)
            ->where('status_inscricao', 'participou')
            ->where('presenca', true)
            ->whereNotNull('presenca_confirmada_em')
            ->whereHas('agenda', fn ($query) => $query->where('evento_id', $evento->evento_id)
                ->where('status_ativo', 'finalizado'))
            ->exists();

        if (! $participou) {
            throw new ValidationConflictException('A pessoa não possui participação confirmada neste evento.');
        }
    }
}
