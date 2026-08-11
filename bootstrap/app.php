<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Estos campos nunca deben quedar en la sesión como "old input" cuando falla el checkout.
        $exceptions->dontFlash([
            'card_holder',
            'card_number',
            'card_expiry',
            'card_cvv',
        ]);
    })->create();

if (getenv('VERCEL')) {
    $storagePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'cafe-salas-storage';

    foreach (['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
        $path = $storagePath.'/'.$directory;

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    $app->useStoragePath($storagePath);
}

return $app;
