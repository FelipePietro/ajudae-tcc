<?php

namespace App\Services;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Models\SolicitacaoLgpd;
use App\Support\AccountAccess;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

final class PrivacyRequests
{
    public const RULES = [
        'tipo' => ['required', 'in:acesso,correcao,exclusao,portabilidade,informacao'],
        'descricao' => ['nullable', 'string', 'max:2000'],
    ];

    public function owner(mixed $account): Pessoa|Ong
    {
        abort_unless($account instanceof Ong || ($account instanceof Pessoa && AccountAccess::hasPersonalJourney($account)), 403);
        abort_unless(AccountAccess::canOperate($account), 403);

        return $account;
    }

    public function history(Pessoa|Ong $account): MorphMany
    {
        return $account->morphMany(SolicitacaoLgpd::class, 'solicitante')
            ->latest()->orderByDesc('solicitacao_lgpd_id');
    }

    public function register(Pessoa|Ong $identity, array $validated): SolicitacaoLgpd
    {
        return DB::transaction(function () use ($identity, $validated): SolicitacaoLgpd {
            $account = $identity->newQuery()->lockForUpdate()->findOrFail($identity->getKey());
            abort_unless((int) $account->auth_version === (int) $identity->auth_version, 403);
            $account = $this->owner($account);

            return $account->morphMany(SolicitacaoLgpd::class, 'solicitante')->create($validated)->refresh();
        }, 3);
    }
}
