<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Erro inesperado ao enviar um formulário: volta para a tela com o que foi
        // digitado e um toast de erro, em vez da página de erro do PHP. O erro
        // continua registrado em storage/logs (o report não passa por aqui).
        // GET, JSON e Livewire seguem o tratamento padrão; validação, login,
        // 403 e 404 já têm resposta própria.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->isMethod('GET') || $request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return null;
            }

            if ($e instanceof TokenMismatchException) {
                return back()->withInput()->with('error', 'A página ficou aberta por muito tempo. Confira os dados e envie de novo.');
            }

            if ($e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof ModelNotFoundException
                || $e instanceof HttpExceptionInterface) {
                return null;
            }

            return back()->withInput()->with('error', 'Não foi possível concluir. Tente de novo; se continuar, avise o suporte.');
        });
    })->create();
