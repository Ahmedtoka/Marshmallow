<?php

namespace App\Http\Middleware;

use App\Support\MetaParams;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Writes Meta's _fbp / _fbc cookies and the tracker's visitor id from the server on public pages.
 * Cookies set by JavaScript last only 7 days in Safari; server cookies last their full age, so an ad
 * click today still matches a booking made next week. Only runs when the Meta pixel is configured.
 */
class SetMetaCookies
{
    /** Same lifetime the tracker gives the visitor id. */
    private const VISITOR_DAYS = 730;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('admin', 'admin/*', 't/*') || ! setting('meta_pixel_id')) {
            return $response;
        }

        foreach (MetaParams::for($request)->getCookiesToSet() as $cookie) {
            $response->headers->setCookie($this->cookie($request, $cookie->name, $cookie->value, $cookie->max_age, $cookie->domain));
        }

        // Refreshed on every page so it keeps its server-set lifetime; the tracker reads the same value.
        $response->headers->setCookie($this->cookie($request, 'mm_vid', MetaParams::visitorId($request), self::VISITOR_DAYS * 86400, null));

        return $response;
    }

    /** Readable by JavaScript on purpose: the pixel and the tracker both need these values. */
    private function cookie(Request $request, string $name, string $value, int $maxAge, ?string $domain): Cookie
    {
        return Cookie::create($name, $value, time() + $maxAge, '/', $domain ?: null, $request->isSecure(), false, true, Cookie::SAMESITE_LAX);
    }
}
