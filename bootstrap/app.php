<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SyncUjianSessions::class,
        ]);
        $middleware->alias([
            'exam.auth' => \App\Http\Middleware\ExamAuth::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if (
                in_array($response->getStatusCode(), [404, 403, 500, 503]) &&
                !request()->expectsJson() &&
                !str_starts_with(request()->path(), 'api/')
            ) {
                return \Inertia\Inertia::render('Error', [
                    'status' => $response->getStatusCode(),
                ])->toResponse(request())
                  ->setStatusCode($response->getStatusCode());
            }
            return $response;
        });
    })->create();
