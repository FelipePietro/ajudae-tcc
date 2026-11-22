<?php

namespace App\Services;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

class AccountCredentials
{
    public function authenticate(string $type, string $login, #[\SensitiveParameter] string $password, ?callable $onSuccess = null): mixed
    {
        $model = $type === 'pessoa' ? Pessoa::class : Ong::class;

        return (new Timebox)->call(function (Timebox $timebox) use ($model, $type, $login, $password, $onSuccess) {
            return DB::transaction(function () use ($model, $type, $login, $password, $onSuccess, $timebox) {
                $account = $model::where("login_{$type}", $login)->lockForUpdate()->first();

                if (! $account || ! Hash::check($password, $account->getAuthPassword()) || ! AccountAccess::canSignIn($account)) {
                    return null;
                }

                $timebox->returnEarly();

                return $onSuccess ? $onSuccess($account) : $account;
            });
        }, 250_000);
    }

    public function update(Pessoa|Ong $actor, array $attributes, #[\SensitiveParameter] ?string $currentPassword): Pessoa|Ong
    {
        return DB::transaction(function () use ($actor, $attributes, $currentPassword) {
            $account = $actor->newQuery()->whereKey($actor->getKey())->lockForUpdate()->firstOrFail();
            if (! AccountAccess::canOperate($account) || (int) $account->auth_version !== (int) $actor->auth_version) {
                throw new AuthorizationException;
            }

            $column = $account->getAuthPasswordName();
            if (array_key_exists($column, $attributes)) {
                if ($currentPassword === null || ! Hash::check($currentPassword, $account->getAuthPassword())) {
                    throw ValidationException::withMessages(['senha_atual' => 'Confirme a senha atual.']);
                }

                $attributes[$column] = Hash::make($attributes[$column]);
                $account->fill($attributes);
                $this->revoke($account);
            } else {
                $account->update($attributes);
            }

            return $account;
        });
    }

    // Caller owns the transaction and account row lock.
    public function revoke(Pessoa|Ong $account): void
    {
        $account->forceFill(['auth_version' => (int) $account->auth_version + 1])->save();
        $account->tokens()->delete();
    }
}
