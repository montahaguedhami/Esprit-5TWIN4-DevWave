<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Canonicalise the loopback hostname before anything else runs, so a
        // page opened on 127.0.0.1 lands on localhost and reCAPTCHA sees the
        // origin its key was issued for. Local environments only.
        $middleware->web(prepend: [
            \App\Http\Middleware\NormalizeLocalhost::class,
        ]);

        // Every auth-protected page ends here once the session is gone.
        // Laravel redirects guests to a route named "login"; this application
        // names it "auth.login", so without this the visitor is sent to a
        // route that does not exist and the request dies with
        // RouteNotFoundException instead of landing on the login form.
        $middleware->redirectGuestsTo(fn () => route('auth.login'));

        // Exclude JWT cookies from Laravel's cookie encryption — tymon reads
        // the raw JWT out of them, so an encrypted value would not decode.
        $middleware->encryptCookies(except: [
            'access_token',
            'refresh_token',
        ]);

        // No route is exempt from CSRF: there is no payment provider, so no
        // server-to-server callback exists that would need it.
        $middleware->alias([            'role'       => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'jwt.auth'   => \App\Http\Middleware\JwtAuthenticate::class,
            'jwt.role'   => \App\Http\Middleware\JwtCheckRole::class,
            'manager'    => \App\Http\Middleware\EnsureManager::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
