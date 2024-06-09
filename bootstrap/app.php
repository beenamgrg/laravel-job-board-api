<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminCheck;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware)
    {
        $middleware->alias(
            [
                'adminCheck' => \App\Http\Middleware\AdminCheck::class,
                'superAdminCheck' => \App\Http\Middleware\SuperAdminCheck::class,

            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions)
    {
        //
    })->create();
