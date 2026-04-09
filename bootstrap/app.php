<?php

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'seller' => \App\Http\Middleware\SellerMiddleware::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'faceid.enrolled' => \App\Http\Middleware\EnsureFaceIdEnrolled::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
