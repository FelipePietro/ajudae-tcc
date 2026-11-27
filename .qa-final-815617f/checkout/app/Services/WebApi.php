<?php

namespace App\Services;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Dispatch only the existing V1 auth contracts, including their middleware. */
class WebApi
{
    public function call(string $type, string $operation, array $input = [], ?string $bearer = null): array
    {
        abort_unless(in_array($type, ['pessoa', 'ong'], true)
            && in_array($operation, ['login', 'me', 'logout'], true), 404);
        $outer = request();
        $guards = config('sanctum.guard');
        $router = app(Router::class);
        $route = $router->getCurrentRoute();
        $segment = $type === 'pessoa' ? 'pessoas' : 'ongs';
        $inner = Request::create('/api/v1/'.$segment.'/'.$operation, $operation === 'me' ? 'GET' : 'POST', $input, [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => $bearer ? 'Bearer '.$bearer : '',
            'REMOTE_ADDR' => $outer->ip(),
            'HTTP_USER_AGENT' => $outer->userAgent() ?? '',
        ]);

        try {
            // Never allow an ambient web identity to replace this Bearer.
            config(['sanctum.guard' => []]);
            Auth::forgetGuards();
            app()->instance('request', $inner);
            try {
                $response = $router->dispatch($inner);
            } catch (ValidationException $exception) {
                return ['status' => 422, 'body' => ['errors' => array_fill_keys(array_keys($exception->errors()), ['Dados inválidos.'])]];
            } catch (Throwable $exception) {
                $response = app(ExceptionHandler::class)->render($inner, $exception);
            }

            return ['status' => $response->getStatusCode(), 'body' => json_decode($response->getContent(), true) ?? []];
        } finally {
            app()->instance('request', $outer);
            config(['sanctum.guard' => $guards]);
            Auth::forgetGuards();
            // Router::dispatch changes the current route; restore the outer context.
            (function ($previous) {
                $this->current = $previous;
            })->call($router, $route);
        }
    }
}
