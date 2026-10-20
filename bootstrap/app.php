<?php

use App\Exceptions\ValidationConflictException;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureOngApproved;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Proxy addresses come from config/trustedproxy.php (also with config cache).
        // Trust only TLS termination; forwarded IP/Host/port/prefix stay ignored.
        $middleware->trustProxies(
            headers: Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->alias([
            // Middleware do Sanctum
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,

            // Middleware administrativo do projeto
            'admin' => AdminMiddleware::class,
            'ong.approved' => EnsureOngApproved::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationConflictException $exception, Request $request) {
            return $request->is('api/*')
                ? ApiResponse::error($exception->getMessage(), null, 409)
                : null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return $request->is('api/*')
                ? ApiResponse::error('Dados inválidos.', $exception->errors(), 422)
                : null;
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return $request->is('api/*')
                ? ApiResponse::error('Não autenticado.', null, 401)
                : null;
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return $request->is('api/*')
                ? ApiResponse::error('Acesso negado.', null, 403)
                : null;
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request) {
            return $request->is('api/*')
                ? ApiResponse::error('Recurso não encontrado.', null, 404)
                : null;
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $exception instanceof ValidationException => ApiResponse::error('Dados inválidos.', $exception->errors(), 422),
                $exception instanceof AuthenticationException => ApiResponse::error('Não autenticado.', null, 401),
                $exception instanceof AuthorizationException => ApiResponse::error('Acesso negado.', null, 403),
                $exception instanceof ModelNotFoundException,
                $exception instanceof NotFoundHttpException => ApiResponse::error('Recurso não encontrado.', null, 404),
                $exception instanceof HttpExceptionInterface => match ($exception->getStatusCode()) {
                    401 => ApiResponse::error('Não autenticado.', null, 401),
                    403 => ApiResponse::error('Acesso negado.', null, 403),
                    404 => ApiResponse::error('Recurso não encontrado.', null, 404),
                    default => ApiResponse::error('Não foi possível concluir a operação.', null, $exception->getStatusCode()),
                },
                default => ApiResponse::error('Erro interno do servidor.', null, 500),
            };
        });
    })
    ->create();
