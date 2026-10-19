<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Acompanhamento;
use App\Models\Evento;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoOrganizador;
use App\Models\VinculoOngOrganizador;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class OrganizadorService
{
    public const PARTICIPACOES_MINIMAS = 5;

    public const COOLDOWN_DIAS = 30;

    public function __construct(
        private readonly ConfiabilidadeService $confiabilidade,
        private readonly AvaliacaoService $avaliacoes,
    ) {}

    public function solicitar(Pessoa $pessoa, string $justificativa): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($pessoa, $justificativa) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $this->garantirElegivel($pessoa);

            return SolicitacaoOrganizador::create([
                'pessoa_id' => $pessoa->pessoa_id,
                'status' => 'pendente',
                'justificativa' => trim($justificativa),
                'solicitada_em' => now(),
            ]);
        });
    }

    public function iniciarAnalise(SolicitacaoOrganizador $solicitacao, Pessoa $admin): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            if ($solicitacao->status !== 'pendente'
                && ! ($solicitacao->status === 'em_analise' && ! $this->lockValido($solicitacao))) {
                throw new ValidationConflictException('A solicitação não pode ser assumida no estado atual.');
            }

            $solicitacao->update([
                'status' => 'em_analise',
                'admin_id' => $admin->pessoa_id,
                'analise_iniciada_em' => now(),
            ]);

            return $solicitacao->refresh();
        });
    }

    public function aprovar(SolicitacaoOrganizador $solicitacao, Pessoa $admin): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirDonoDoLock($solicitacao, $admin);

            $pessoa = Pessoa::whereKey($solicitacao->pessoa_id)->lockForUpdate()->firstOrFail();
            $this->garantirElegivel($pessoa, $solicitacao->solicitacao_organizador_id);

            $pessoa->forceFill(['role_pessoa' => 'organizador'])->save();
            $solicitacao->update([
                'status' => 'aprovada',
                'motivo_decisao' => null,
                'decidida_em' => now(),
            ]);

            return $solicitacao->refresh();
        });
    }

    public function reprovar(SolicitacaoOrganizador $solicitacao, Pessoa $admin, string $motivo): SolicitacaoOrganizador
    {
        return DB::transaction(function () use ($solicitacao, $admin, $motivo) {
            $solicitacao = SolicitacaoOrganizador::whereKey($solicitacao->getKey())->lockForUpdate()->firstOrFail();

            $this->garantirDonoDoLock($solicitacao, $admin);

            $solicitacao->update([
                'status' => 'reprovada',
                'motivo_decisao' => trim($motivo),
                'decidida_em' => now(),
            ]);

            return $solicitacao->refresh();
        });
    }

    private function garantirDonoDoLock(SolicitacaoOrganizador $solicitacao, Pessoa $admin): void
    {
        if ($solicitacao->status !== 'em_analise'
            || $solicitacao->admin_id === null
            || (int) $solicitacao->admin_id !== (int) $admin->pessoa_id
            || ! $this->lockValido($solicitacao)) {
            throw new ValidationConflictException('A análise deve ser concluída pelo Admin que possui o lock válido.');
        }
    }

    private function lockValido(SolicitacaoOrganizador $solicitacao): bool
    {
        return $solicitacao->analise_iniciada_em !== null
            && now()->lessThan($solicitacao->analise_iniciada_em->copy()->addHours(24));
    }

    public function garantirElegivel(Pessoa $pessoa, ?int $ignorarSolicitacao = null): void
    {
        if ($pessoa->role_pessoa !== 'voluntario' || $pessoa->status_validacao !== 'aprovado') {
            throw new AuthorizationException('Somente voluntários aprovados podem solicitar promoção.');
        }

        $resumo = $this->confiabilidade->resumo($pessoa);
        $participacoes = $resumo['participacoes_confirmadas'];

        if ($participacoes < self::PARTICIPACOES_MINIMAS) {
            throw new ValidationConflictException('São necessárias ao menos 5 participações confirmadas.');
        }

        if (! $resumo['formada'] || $resumo['score'] < 80) {
            throw new ValidationConflictException('A confiabilidade precisa ser de ao menos 80.');
        }

        $media = $this->avaliacoes->mediaVoluntario($pessoa);
        if ($media === null || $media < 4.0) {
            throw new ValidationConflictException('A média de avaliações precisa ser de ao menos 4,0.');
        }

        if ($pessoa->bloqueado_ate?->isFuture()) {
            throw new ValidationConflictException('A pessoa possui bloqueio ativo.');
        }

        if (Acompanhamento::where('padrinho_id', $pessoa->pessoa_id)->where('status', 'ativo')->exists()) {
            throw new ValidationConflictException('A pessoa possui apadrinhado ativo.');
        }

        $naoConcluida = SolicitacaoOrganizador::where('pessoa_id', $pessoa->pessoa_id)
            ->whereIn('status', ['pendente', 'em_analise'])
            ->when($ignorarSolicitacao, fn ($query) => $query->whereKeyNot($ignorarSolicitacao))
            ->exists();

        if ($naoConcluida) {
            throw new ValidationConflictException('Já existe uma solicitação não concluída.');
        }

        $ultimaReprovacao = SolicitacaoOrganizador::where('pessoa_id', $pessoa->pessoa_id)
            ->where('status', 'reprovada')
            ->whereNotNull('decidida_em')
            ->latest('decidida_em')
            ->first();

        if ($ultimaReprovacao?->decidida_em?->greaterThan(now()->subDays(self::COOLDOWN_DIAS))) {
            throw new ValidationConflictException('Nova solicitação disponível após 30 dias completos da reprovação.');
        }
    }

    public function convidar(Ong $ong, Pessoa $pessoa): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($ong, $pessoa) {
            $ong = Ong::whereKey($ong->getKey())->lockForUpdate()->firstOrFail();
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();

            if ($ong->status_ong !== 'ativo') {
                throw new AuthorizationException('A ONG precisa estar ativa.');
            }

            if ($pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado') {
                throw new ValidationConflictException('Somente organizadores aprovados podem ser convidados.');
            }

            if (VinculoOngOrganizador::where('pessoa_id', $pessoa->pessoa_id)->where('status', 'ativo')->exists()) {
                throw new ValidationConflictException('O organizador já possui vínculo ativo com uma ONG.');
            }

            if (VinculoOngOrganizador::where('ong_id', $ong->ong_id)
                ->where('pessoa_id', $pessoa->pessoa_id)
                ->where('status', 'convite_pendente')
                ->exists()) {
                throw new ValidationConflictException('Já existe convite pendente para este organizador.');
            }

            return VinculoOngOrganizador::create([
                'ong_id' => $ong->ong_id,
                'pessoa_id' => $pessoa->pessoa_id,
                'status' => 'convite_pendente',
                'convidado_em' => now(),
            ]);
        });
    }

    public function responderConvite(VinculoOngOrganizador $vinculo, Pessoa $pessoa, bool $aceitar): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($vinculo, $pessoa, $aceitar) {
            $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
            $vinculo = VinculoOngOrganizador::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();

            if ($vinculo->pessoa_id !== $pessoa->pessoa_id) {
                throw new AuthorizationException('Este convite pertence a outra pessoa.');
            }

            if ($vinculo->status !== 'convite_pendente') {
                throw new ValidationConflictException('Este convite já foi respondido.');
            }

            if ($aceitar) {
                $ong = Ong::whereKey($vinculo->ong_id)->lockForUpdate()->firstOrFail();
                if ($pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado' || $ong->status_ong !== 'ativo') {
                    throw new ValidationConflictException('O convite não pode ser aceito no estado atual.');
                }

                if (VinculoOngOrganizador::where('pessoa_id', $pessoa->pessoa_id)
                    ->where('status', 'ativo')
                    ->whereKeyNot($vinculo->getKey())
                    ->exists()) {
                    throw new ValidationConflictException('O organizador já possui vínculo ativo com uma ONG.');
                }

                if (Evento::where('pessoa_id', $pessoa->pessoa_id)
                    ->whereNull('ong_id')
                    ->whereNotIn('status_evento', ['finalizado', 'cancelado'])
                    ->exists()) {
                    throw new ValidationConflictException('O organizador possui evento independente não terminal.');
                }
            }

            $vinculo->update([
                'status' => $aceitar ? 'ativo' : 'recusado',
                'respondido_em' => now(),
            ]);

            return $vinculo->refresh();
        });
    }

    public function encerrarVinculo(VinculoOngOrganizador $vinculo, Pessoa|Ong $ator): VinculoOngOrganizador
    {
        return DB::transaction(function () use ($vinculo, $ator) {
            $vinculo = VinculoOngOrganizador::whereKey($vinculo->getKey())->lockForUpdate()->firstOrFail();

            $autorizado = $ator instanceof Ong
                ? $vinculo->ong_id === $ator->ong_id
                : $vinculo->pessoa_id === $ator->pessoa_id;

            if (! $autorizado) {
                throw new AuthorizationException('Este vínculo não pertence ao usuário autenticado.');
            }

            if ($vinculo->status !== 'ativo') {
                throw new ValidationConflictException('O vínculo não está ativo.');
            }

            $possuiEventoNaoTerminal = Evento::where('ong_id', $vinculo->ong_id)
                ->where('responsavel_pessoa_id', $vinculo->pessoa_id)
                ->whereNotIn('status_evento', ['finalizado', 'cancelado'])
                ->exists();

            if ($possuiEventoNaoTerminal) {
                throw new ValidationConflictException('O organizador ainda é responsável por evento não terminal.');
            }

            $vinculo->update([
                'status' => 'encerrado',
                'encerrado_em' => now(),
            ]);

            return $vinculo->refresh();
        });
    }

    public function atribuirResponsavel(Evento $evento, Ong $ong, ?Pessoa $pessoa, EventoOperationalService $operations): Evento
    {
        return DB::transaction(function () use ($evento, $ong, $pessoa, $operations) {
            $evento = Evento::whereKey($evento->getKey())->lockForUpdate()->firstOrFail();

            if ($evento->ong_id !== $ong->ong_id) {
                throw new AuthorizationException('A ONG não é proprietária deste evento.');
            }

            if (in_array($evento->status_evento, ['cancelado', 'finalizado'], true)) {
                throw new ValidationConflictException('Evento terminal não pode alterar o responsável.');
            }

            if ($evento->agendas()->where('data_inicio', '<=', now())->exists()) {
                throw new ValidationConflictException('O responsável não pode ser alterado após o início do evento.');
            }

            if ($pessoa) {
                $pessoa = Pessoa::whereKey($pessoa->getKey())->lockForUpdate()->firstOrFail();
                $vinculoAtivo = VinculoOngOrganizador::where('ong_id', $ong->ong_id)
                    ->where('pessoa_id', $pessoa->pessoa_id)
                    ->where('status', 'ativo')
                    ->exists();

                if ($pessoa->role_pessoa !== 'organizador' || $pessoa->status_validacao !== 'aprovado' || ! $vinculoAtivo) {
                    throw new ValidationConflictException('O responsável deve ser organizador aprovado e vinculado à ONG.');
                }
            }

            if ($evento->responsavel_pessoa_id !== $pessoa?->pessoa_id) {
                $evento->forceFill(['responsavel_pessoa_id' => $pessoa?->pessoa_id])->save();
                $operations->exigirReaprovacao($evento);
                $operations->notificarEvento($evento, 'evento.alterado', "O responsável do evento {$evento->nm_evento} foi alterado.");
            }

            return $evento->refresh();
        });
    }
}
