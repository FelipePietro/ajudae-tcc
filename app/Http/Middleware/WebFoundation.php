<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebFoundation
{
    public function handle(Request $request, Closure $next)
    {
        // Clean payloads left by the old mock, including old flashed input.
        $request->session()->forget(['cadastro_pessoa_mock', 'assinatura_pessoa_mock']);
        $request->session()->forget('_old_input');
        abort_unless(in_array(config('session.driver'), ['file', 'database', 'redis', 'array'], true), 503);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
