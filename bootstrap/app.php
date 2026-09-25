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

        // Tracker and Meta pixel cookies are written by JavaScript in plain text; without this Laravel reads
        // them as null and website leads lose their visitor journey and their Meta ad-click match.
        $middleware->encryptCookies(except: ['mm_vid', 'mm_sid', 'mm_fbclid', '_fbp', '_fbc']);

        // Meta's _fbp / _fbc and the visitor id, written from the server on public pages.
        $middleware->web(append: [\App\Http\Middleware\SetMetaCookies::class]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
