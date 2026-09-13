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
            'role' => \App\Http\Middleware\EnsureRole::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // The tracker posts with navigator.sendBeacon, which cannot attach a CSRF header.
        $middleware->validateCsrfTokens(except: ['t/collect']);

        // Tracker cookies are written by JavaScript in plain text; without this Laravel reads them as null
        // and website leads lose their link to the visitor journey.
        $middleware->encryptCookies(except: ['mm_vid', 'mm_sid']);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
