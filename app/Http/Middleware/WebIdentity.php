<?php

namespace App\Http\Middleware;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Services\WebApi;
use App\Services\WebSession;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class WebIdentity
{
    public function handle(Request $request, Closure $next, string $kind = 'any')
    {
        $session = app(WebSession::class);
        $type = $request->session()->get('bff.type');
        $bearer = $session->bearer($request);
        if (! in_array($type, ['pessoa', 'ong'], true) || ! $bearer
            || (int) $request->session()->get('bff.expires') <= now()->getTimestamp()) {
            $session->end($request);

            return redirect()->route('login')->with('notice', 'Sessão expirada. Entre novamente.');
        }
        $result = app(WebApi::class)->call($type, 'me', bearer: $bearer);
        if ($result['status'] === 401) {
            $session->end($request);

            return redirect()->route('login')->with('notice', 'Sessão expirada. Entre novamente.');
        }
        abort_unless($result['status'] === 200, $result['status'] === 403 ? 403 : 503);
        $identity = PersonalAccessToken::findToken($bearer)?->tokenable;
        abort_unless(($type === 'pessoa' && $identity instanceof Pessoa)
            || ($type === 'ong' && $identity instanceof Ong), 403);
        $request->setUserResolver(fn () => $identity);
        $request->attributes->set('bff_account', $result['body']['data']);
        abort_if($kind === 'pessoa' && ! $identity instanceof Pessoa, 403);
        abort_if($kind === 'ong' && ! $identity instanceof Ong, 403);
        if ($kind === 'admin') {
            abort_unless($identity instanceof Pessoa && $identity->role_pessoa === 'administrador', 403);
            $permission = match ($request->route()->getName()) {
                'admin.candidatos' => 'usuarios.validar',
                'admin.cadastros-ong' => 'ongs.validar',
                'admin.fila-eventos' => 'eventos.aprovar',
                'admin.upgrades-org' => 'organizadores.validar',
                'admin.solicitacoes-lgpd' => 'lgpd.visualizar',
                'admin.usuarios' => 'usuarios.moderar',
                'admin.log-acoes' => 'admin.logs.visualizar',
                default => null,
            };
            abort_if($permission && ! $identity->hasPermission($permission), 403);
        }

        return $next($request);
    }
}
