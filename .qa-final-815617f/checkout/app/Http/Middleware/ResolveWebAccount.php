<?php

namespace App\Http\Middleware;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveWebAccount
{
    public function handle(Request $request, Closure $next, ?string $required = null): Response
    {
        abort_if(in_array(config('session.driver'), ['cookie', 'array'], true) && ! app()->environment('testing'), 503);
        $session = $request->session();
        $public = $required === 'public';
        $personal = $required === 'jornada-pessoa';
        $requiredType = $personal ? 'pessoa' : $required;
        $identity = $session->get('web_identity');
        $type = $identity['type'] ?? null;
        $pessoa = Auth::guard('web_pessoa');
        $ong = Auth::guard('web_ong');

        $authenticated = false;
        if ($identity !== null || $session->hasAny([$pessoa->getName(), $ong->getName()])) {
            $now = now()->timestamp;
            $account = match ($type) {
                'pessoa' => $session->has($pessoa->getName()) && ! $session->has($ong->getName()) ? $pessoa->user() : null,
                'ong' => $session->has($ong->getName()) && ! $session->has($pessoa->getName()) ? $ong->user() : null,
                default => null,
            };
            $validType = ($type === 'pessoa' && $account instanceof Pessoa) || ($type === 'ong' && $account instanceof Ong);

            if (! $validType || $account->auth_version === null
                || $account->getKey() !== ($identity['id'] ?? null)
                || $account->auth_version !== ($identity['version'] ?? null)
                || ! AccountAccess::canSignIn($account) || self::expired($identity, $now)) {
                $session->invalidate();
                $session->regenerateToken();

                if ($public) {
                    return $this->privateResponse($next($request));
                }

                return $this->privateResponse($request->expectsJson()
                    ? ApiResponse::error('Sua sessão terminou. Entre novamente.', status: 401)
                    : redirect()->route('login')->with('status', 'Sua sessão terminou. Entre novamente.'));
            }

            $request->attributes->set('web_account', $account);

            if (! $public && $required !== null && $required !== 'required' && $requiredType !== $type) {
                abort(403);
            }

            if (! $public && $account->exclusao_pendente && ! $request->routeIs('login', 'login.store', 'logout', 'conta', 'identity.deletion.cancel')) {
                return $this->privateResponse($request->expectsJson()
                    ? ApiResponse::error('Acompanhe a solicitação de exclusão em Minha conta.', status: 403)
                    : redirect()->route('conta'));
            }

            abort_if($personal && ! AccountAccess::hasPersonalJourney($account), 403);

            $authenticated = true;
        } elseif (! $public && $required !== null) {
            return $this->privateResponse($request->expectsJson()
                ? ApiResponse::error('Não autenticado.', status: 401)
                : redirect()->route('login'));
        }

        $response = $next($request);

        if ($authenticated && ! $public && $response->isSuccessful() && ! $request->isMethod('HEAD')
            && ! str_contains($request->header('Sec-Purpose', ''), 'prefetch')
            && ! str_contains($request->header('Purpose', ''), 'prefetch')
            && $session->get('web_identity') === $identity) {
            $identity['last_activity'] = $now;
            $session->put('web_identity', $identity);
        }

        return $this->privateResponse($response);
    }

    public static function expired(array $identity, int $now): bool
    {
        $started = $identity['started_at'] ?? null;
        $activity = $identity['last_activity'] ?? null;

        return ! is_int($started) || ! is_int($activity)
            || $started > $activity || $activity > $now
            || $now - $started >= config('identity.absolute_seconds')
            || $now - $activity >= config('identity.idle_seconds');
    }

    private function privateResponse(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
