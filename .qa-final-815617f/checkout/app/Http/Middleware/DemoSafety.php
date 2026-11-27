<?php

namespace App\Http\Middleware;

use App\Support\NavigationContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/** Frontend demonstration only: no authentication, API calls or account writes. */
class DemoSafety
{
    public function handle(Request $request, Closure $next): Response
    {
        // Discard legacy navigation payloads without reading/revoking real tokens.
        $request->session()->forget(['cadastro_pessoa_mock', 'assinatura_pessoa_mock', '_old_input', 'bff']);
        NavigationContext::preparePresentation($request);

        try {
            $response = $next($request);
        } catch (ValidationException $exception) {
            // Do not flash submitted fields, documents, passwords or personal data.
            $target = match ($request->route()->getName()) {
                'cadastro.pessoa.finalizar' => route('cadastro.pessoa'),
                'ong.cadastrar' => route('ong.cadastrar.form'),
                'ong.perfil.atualizar' => route('ong.perfil.editar'),
                default => route('inicio'),
            };
            $response = redirect($target)->withErrors($exception->errors());
        }

        $request->session()->forget('_old_input');
        $response->headers->set('X-Ajudae-Demo', 'true');
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
