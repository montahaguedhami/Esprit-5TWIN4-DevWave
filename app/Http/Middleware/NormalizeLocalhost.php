<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Point localhost → 127.0.0.1 are two different names for the same machine,
 * but they are NOT the same origin as far as third parties are concerned.
 *
 * Google reCAPTCHA validates the page origin against the list of domains
 * declared on the key. A key authorised for `localhost` therefore refuses to
 * draw its widget when the page is served from `127.0.0.1`, and shows
 * "This host is not in the list of accepted domains instead of the checkbox.
 * Nothing in the application is wrong at that point: the two URLs only need
 * to agree on a single name.
 *
 * Rather than asking every developer to remember which spelling the key was
 * issued for, the canonical name is chosen here and the other one is
 * redirected to it, preserving scheme, port, path and query string.
 *
 * Local environments only: on a real deployment the hostname is meaningful
 * and rewriting it would break the application.
 */
class NormalizeLocalhost
{
    /** Hostnames that are aliases of the loopback interface. */
    private const ALIASES = ['127.0.0.1', '[::1]', '::1'];

    /** The name Google reCAPTCHA is expected to have been issued for. */
    private const CANONICAL = 'localhost';

    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal() || ! in_array($request->getHost(), self::ALIASES, true)) {
            return $next($request);
        }

        $port     = $request->getPort();
        $authority = self::CANONICAL.(! in_array($port, [80, 443], true) ? ':'.$port : '');

        return redirect()
            ->to($request->getScheme().'://'.$authority.$request->getRequestUri(), 302);
    }
}
