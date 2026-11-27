<?php

namespace App\Services;

use App\Exceptions\ValidationConflictException;
use App\Models\Acompanhamento;
use App\Models\AdminLog;
use App\Models\Evento;
use App\Models\Inscricao;
use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoOrganizador;
use App\Models\SolicitacaoPadrinho;
use App\Models\VinculoOngOrganizador;
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class AccountDeletion
{
    public function __construct(private AccountCredentials $credentials) {}

    public function schedule(Pessoa|Ong $actor): Pessoa|Ong
    {
        return DB::transaction(function () use ($actor) {
            $account = $this->lock($actor);
            if (! AccountAccess::mayRequestOwnDeletion($account)) {
                throw new AuthorizationException;
            }
            if ($account->exclusao_pendente) {
                throw new ValidationConflictException('A exclusão desta conta já está agendada.');
            }
            if (! AccountAccess::canOperate($account)) {
                throw new AuthorizationException;
            }
            if ($this->hasActiveProcesses($account)) {
                throw new ValidationConflictException('Resolva os processos ativos antes de solicitar a exclusão.');
            }

            $account->forceFill(['exclusao_pendente' => true, 'deletar_em' => now()->addDays(7)]);
            $this->credentials->revoke($account);
            $this->audit($account, 'conta.exclusao_agendada', null);

            return $account;
        }, 3);
    }

    public function cancel(Pessoa|Ong $actor): void
    {
        DB::transaction(function () use ($actor) {
            $account = $this->lock($actor);
            if (! $account->exclusao_pendente || ! AccountAccess::canSignIn($account)) {
                throw new ValidationConflictException('A solicitação não pode ser cancelada.');
            }

            $prazoAnterior = $account->deletar_em?->toISOString();
            $account->forceFill(['exclusao_pendente' => false, 'deletar_em' => null]);
            $this->credentials->revoke($account);
            $this->audit($account, 'conta.exclusao_cancelada', $prazoAnterior);
        }, 3);
    }

    private function lock(Pessoa|Ong $actor): Pessoa|Ong
    {
        $account = $actor->newQuery()->whereKey($actor->getKey())->lockForUpdate()->firstOrFail();
        if ((int) $account->auth_version !== (int) $actor->auth_version) {
            throw new AuthorizationException;
        }

        return $account;
    }

    private function hasActiveProcesses(Pessoa|Ong $account): bool
    {
        $id = $account->getKey();
        $events = Evento::whereNotIn('status_evento', ['finalizado', 'cancelado', 'reprovado']);
        $links = VinculoOngOrganizador::whereIn('status', ['ativo', 'convite_pendente']);

        if ($account instanceof Ong) {
            return $events->where('ong_id', $id)->exists() || $links->where('ong_id', $id)->exists();
        }

        return ($account->status_validacao === 'em_analise' && $account->analise_assumida_em?->gt(now()->subHours(24)))
            || $events->where(fn ($query) => $query->where('pessoa_id', $id)->orWhere('responsavel_pessoa_id', $id))->exists()
            || $links->where('pessoa_id', $id)->exists()
            || Inscricao::where('pessoa_id', $id)->whereIn('status_inscricao', ['pendente', 'aprovado', 'participando'])
                ->whereHas('agenda', fn ($query) => $query->where('status_ativo', 'ativo')
                    ->whereHas('evento', fn ($evento) => $evento->whereNotIn('status_evento', ['finalizado', 'cancelado', 'reprovado'])))
                ->exists()
            || SolicitacaoOrganizador::where('pessoa_id', $id)->whereIn('status', ['pendente', 'em_analise'])->exists()
            || Acompanhamento::where('status', 'ativo')
                ->where(fn ($query) => $query->where('padrinho_id', $id)->orWhere('apadrinhado_id', $id))->exists()
            || SolicitacaoPadrinho::where('status', 'pendente')
                ->where(fn ($query) => $query->where('padrinho_id', $id)->orWhere('apadrinhado_id', $id))->exists();
    }

    private function audit(Pessoa|Ong $account, string $action, ?string $prazoAnterior): void
    {
        AdminLog::create([
            'pessoa_id' => $account instanceof Pessoa ? $account->getKey() : null,
            'acao' => $action,
            'recurso_type' => $account->getMorphClass(),
            'recurso_id' => $account->getKey(),
            'detalhes' => [
                'exclusao_pendente_antes' => ! $account->exclusao_pendente,
                'exclusao_pendente_depois' => (bool) $account->exclusao_pendente,
                'deletar_em_anterior' => $prazoAnterior,
                'deletar_em' => $account->deletar_em?->toISOString(),
            ],
        ]);
    }
}
