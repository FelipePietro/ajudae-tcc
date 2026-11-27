<?php

namespace App\Http\Middleware;

use App\Models\Ong;
use App\Models\Pessoa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class UseResolvedWebActor
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->attributes->get('web_account');
        abort_unless($actor instanceof Pessoa || $actor instanceof Ong, 401);
        $resolveGuard = $request->getUserResolver();
        $request->setUserResolver(static fn (?string $guard = null) => $guard === null ? $actor : $resolveGuard($guard));

        return $next($request);
    }
}
