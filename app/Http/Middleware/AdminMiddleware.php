<?php

namespace App\Http\Middleware;

use App\Models\Pessoa;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ?string $permission = null)
    {
        $user = $request->user();

        if (! ($user instanceof Pessoa)) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        $authorized = $permission
            ? $user->hasPermission($permission)
            : $user->role_pessoa === 'administrador';

        if (! $authorized) {
            return ApiResponse::error('Acesso negado.', null, 403);
        }

        return $next($request);
    }
}
