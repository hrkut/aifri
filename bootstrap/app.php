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
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'auth.recordings' => \App\Http\Middleware\AuthenticateRecordings::class,
            'admin.conference' => \App\Http\Middleware\UseAdminConference::class,
        ]);

        // The admin's selected edition must be known before route model binding,
        // because Registration/Question lookups are scoped to it.
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\UseAdminConference::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
