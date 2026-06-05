<?php

use App\Http\Middleware\CheckSchedulerHeartbeat;
use App\Http\Middleware\ExamAuth;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SyncUjianSessions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            HandleInertiaRequests::class,
            CheckSchedulerHeartbeat::class,
        ]);
        $middleware->alias([
            'exam.auth' => ExamAuth::class,
            'sync.ujian' => SyncUjianSessions::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            //
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            if (
                in_array($response->getStatusCode(), [404, 403, 500, 503]) &&
                ! request()->expectsJson() &&
                ! str_starts_with(request()->path(), 'api/')
            ) {
                return Inertia::render('Error', [
                    'status' => $response->getStatusCode(),
                ])->toResponse(request())
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
