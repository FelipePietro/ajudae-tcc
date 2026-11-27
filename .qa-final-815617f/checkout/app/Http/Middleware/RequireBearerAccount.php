<?php

namespace App\Http\Middleware;

use App\Models\Ong;
use App\Models\Pessoa;
use App\Support\AccountAccess;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RequireBearerAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        if ((! $account instanceof Pessoa && ! $account instanceof Ong)
            || ! $account->currentAccessToken() instanceof PersonalAccessToken) {
            return ApiResponse::error('Não autenticado.', null, 401);
        }

        $type = $account instanceof Pessoa ? 'pessoa' : 'ong';
        if (! $account->tokenCan($type)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $route = $request->route()->getName();
        if ($route !== "api.{$type}.logout" && ! AccountAccess::canSignIn($account)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        if ($account->exclusao_pendente && ! in_array($route, [
            "api.{$type}.me", "api.{$type}.cancel-deletion", "api.{$type}.logout",
        ], true)) {
            return ApiResponse::error('Acesso restrito durante a exclusão da conta.', null, 403);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
