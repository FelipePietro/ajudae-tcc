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
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvaliacaoService
{
    private const JANELA_AVALIACAO_ONG_DIAS = 7;

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
            if ($evento->status_evento !== 'ativo') {
                throw new ValidationConflictException('Somente evento ativo recebe avaliações.');
            }
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
            $pessoa = Pessoa::whereKey($voluntario->getKey())->lockForUpdate()->firstOrFail();
            if (! $pessoa->isApprovedVolunteer() || ! AccountAccess::canOperate($pessoa)
                || (int) $pessoa->auth_version !== (int) $voluntario->auth_version) {
                throw new AuthorizationException('Somente o voluntário participante pode avaliar a ONG.');
            }
            if ($evento->ong_id === null) {
                throw new ValidationConflictException('Evento independente não possui ONG para avaliar.');
            }
            if (! in_array($evento->status_evento, ['ativo', 'finalizado'], true)) {
                throw new ValidationConflictException('Somente evento ativo recebe avaliações.');
            }
            if ($evento->status_evento === 'finalizado' && ! $this->aceitaAvaliacaoOng($evento)) {
                throw new ValidationConflictException('O prazo para avaliar a ONG está encerrado ou não pôde ser confirmado.');
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

    public function consultarOng(Evento $evento, Pessoa $pessoa): AvaliacaoOng
    {
        if (! AccountAccess::hasPersonalJourney($pessoa) || ! AccountAccess::canOperate($pessoa)) {
            throw new AuthorizationException('Somente a própria Pessoa pode consultar a avaliação enviada.');
        }

        return AvaliacaoOng::where('evento_id', $evento->evento_id)
            ->where('pessoa_id', $pessoa->pessoa_id)
            ->firstOrFail();
    }

    public function prazoAvaliacaoOng(Evento $evento): ?Carbon
    {
        return $evento->finalizado_em === null
            ? null
            : Carbon::parse($evento->finalizado_em)->utc()->addDays(self::JANELA_AVALIACAO_ONG_DIAS);
    }

    public function aceitaAvaliacaoOng(Evento $evento): bool
    {
        $prazo = $this->prazoAvaliacaoOng($evento);

        return $evento->status_evento === 'ativo'
            || ($evento->status_evento === 'finalizado' && $prazo !== null && now()->lessThanOrEqualTo($prazo));
    }

    private function autorizarResponsavel(Evento $evento, Pessoa|Ong $ator): void
    {
        if (! app(EventoPolicy::class)->operate($ator, $evento, true)) {
            throw new AuthorizationException('Somente o responsável operacional pode avaliar o voluntário.');
        }
    }

    private function garantirEventoConcluidoEParticipacao(Evento $evento, Pessoa $voluntario): void
    {
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
