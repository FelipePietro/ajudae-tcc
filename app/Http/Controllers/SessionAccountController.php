<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationConflictException;
use App\Models\Pessoa;
use App\Services\AccountCredentials;
use App\Services\AccountDeletion;
use App\Support\AccountAccess;
use App\Support\WebNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SessionAccountController extends Controller
{
    public function loginForm(Request $request)
    {
        return $request->attributes->has('web_account')
            ? redirect()->route(WebNavigation::for($request)->destination())
            : view('login', ['integratedLogin' => true]);
    }

    public function login(Request $request, AccountCredentials $credentials)
    {
        // A new submission is an explicit switch. Discard the old identity before validating.
        if ($request->attributes->has('web_account')) {
            $this->endSession($request);
        }

        $input = $request->validate([
            'tipo_login' => ['required', Rule::in(['pessoa', 'ong'])],
            'login' => ['required', 'string', 'max:64'],
            'senha' => ['required', 'string', 'max:4096'],
        ]);

        $type = $input['tipo_login'];
        $ipKey = 'web-login-ip:'.hash_hmac('sha256', (string) $request->ip(), config('app.key'));
        $pairKey = 'web-login-pair:'.hash_hmac('sha256', $type.'|'.Str::lower($input['login']).'|'.$request->ip(), config('app.key'));

        foreach ([$ipKey => 100, $pairKey => 5] as $key => $max) {
            if (RateLimiter::hit($key, 15 * 60) > $max) {
                return response()->view('login', [
                    'integratedLogin' => true,
                    'loginError' => 'Aguarde alguns minutos antes de tentar novamente.',
                ], 429, ['Retry-After' => RateLimiter::availableIn($key)]);
            }

        }

        $account = $credentials->authenticate($type, $input['login'], $input['senha']);
        if (! $account) {
            throw ValidationException::withMessages(['login' => 'Não foi possível entrar com os dados informados.']);
        }

        abort_if($account->auth_version === null, 503);
        RateLimiter::clear($pairKey);
        Auth::guard('web_'.$type)->login($account, false);
        $now = now()->timestamp;
        $request->session()->put('web_identity', [
            'type' => $type,
            'id' => $account->getKey(),
            'version' => $account->auth_version,
            'started_at' => $now,
            'last_activity' => $now,
        ]);

        $request->attributes->set('web_account', $account);

        return redirect()->route(WebNavigation::for($request)->destination());
    }

    public function account(Request $request)
    {
        $account = $request->attributes->get('web_account');

        return view('auth.account', [
            'pendingDeletion' => $account->exclusao_pendente,
            'canRequestDeletion' => AccountAccess::mayRequestOwnDeletion($account),
            'deadline' => $account->deletar_em,
            'name' => $account->exclusao_pendente ? null : ($account instanceof Pessoa ? $account->nm_pessoa : $account->nome_fantasia),
            'accountStatus' => $account->exclusao_pendente ? null : ($account instanceof Pessoa ? $account->status_validacao : $account->status_ong),
        ]);
    }

    public function password(Request $request, AccountCredentials $credentials)
    {
        $input = $request->validate([
            'senha_atual' => ['required', 'string', 'max:4096'],
            'senha' => ['required', 'string', 'min:8', 'max:4096', 'confirmed'],
        ]);
        $account = $request->attributes->get('web_account');
        $credentials->update($account, [$account->getAuthPasswordName() => $input['senha']], $input['senha_atual']);
        $this->endSession($request);

        return redirect()->route('login')->with('status', 'Senha alterada. Entre novamente.');
    }

    public function scheduleDeletion(Request $request, AccountDeletion $deletions)
    {
        $account = $request->attributes->get('web_account');
        abort_unless(AccountAccess::mayRequestOwnDeletion($account), 403);

        $request->validate(['confirmar_exclusao' => ['accepted']]);

        try {
            $deletions->schedule($account);
        } catch (ValidationConflictException $exception) {
            throw ValidationException::withMessages(['exclusao' => $exception->getMessage()]);
        }

        $this->endSession($request);

        return redirect()->route('login')->with('status', 'Solicitação registrada. Entre novamente para acompanhar.');
    }

    public function cancelDeletion(Request $request, AccountDeletion $deletions)
    {
        try {
            $deletions->cancel($request->attributes->get('web_account'));
        } catch (ValidationConflictException $exception) {
            throw ValidationException::withMessages(['exclusao' => $exception->getMessage()]);
        }

        $this->endSession($request);

        return redirect()->route('login')->with('status', 'Exclusão cancelada. Entre novamente.');
    }

    public function logout(Request $request)
    {
        $this->endSession($request);

        return redirect()->route('login')->with('status', 'Você saiu da sua conta.');
    }

    private function endSession(Request $request): void
    {
        $type = $request->session()->get('web_identity.type');
        if (in_array($type, ['pessoa', 'ong'], true)) {
            Auth::guard('web_'.$type)->logoutCurrentDevice();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->attributes->remove('web_account');
        $request->attributes->remove(WebNavigation::class);
    }
}
