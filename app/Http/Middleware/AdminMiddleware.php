<?php

namespace App\Http\Middleware;

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
    public function handle($request, Closure $next)
{
    $user = $request->user();

    if (!$user || $user->role_pessoa !== 'administrador') {
        return response()->json([
            'message' => 'Acesso negado.'
        ], 403);
    }

    return $next($request);
}
}
