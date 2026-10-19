<?php

namespace App\Http\Middleware;

use App\Models\Ong;
use App\Support\ApiResponse;
use Closure;

class EnsureOngApproved
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user instanceof Ong && $user->status_ong !== 'ativo') {
            return ApiResponse::error(
                'A ONG precisa estar aprovada para realizar esta operação.',
                null,
                403
            );
        }

        return $next($request);
    }
}
