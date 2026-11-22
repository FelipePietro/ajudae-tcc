<?php

namespace App\Support;

use App\Models\Ong;
use App\Models\Pessoa;

class AccountAccess
{
    public static function canSignIn(Pessoa|Ong $account): bool
    {
        $login = $account instanceof Pessoa ? $account->login_pessoa : $account->login_ong;
        if (str_starts_with(strtolower((string) $login), 'qa72.') && ! app()->environment('local', 'testing')) {
            return false;
        }

        if ($account instanceof Pessoa && $account->status_validacao === 'reprovado_definitivamente') {
            return false;
        }

        return ! $account->exclusao_pendente
            || ($account->deletar_em !== null && now()->lt($account->deletar_em));
    }

    public static function canOperate(Pessoa|Ong $account): bool
    {
        return ! $account->exclusao_pendente && self::canSignIn($account);
    }

    public static function activeOng(Ong $ong): bool
    {
        return $ong->status_ong === 'ativo' && self::canOperate($ong);
    }

    public static function deletionStatus(Pessoa|Ong $account): array
    {
        return ['exclusao_pendente' => true, 'deletar_em' => $account->deletar_em];
    }
}
