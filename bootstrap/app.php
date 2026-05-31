<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckInstalled;
use App\Http\Middleware\SetUserTimezone;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // تطبيق الـ middleware على كل الـ web routes
        $middleware->web(append: [
            CheckInstalled::class,
            SetUserTimezone::class,
        ]);

        // تعريف aliases للاستخدام في routes
        $middleware->alias([
            'admin'  => \App\Http\Middleware\AdminOnly::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
