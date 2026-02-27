<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Basic hardening headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS only when connection is HTTPS to avoid issues on plain HTTP dev
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        // Moderate CSP tuned for this app: allows our domain + needed CDNs
        $csp = implode(' ', [
            "default-src 'self'",
            "; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "; img-src 'self' data: blob: https://cdn.jsdelivr.net",
            "; connect-src 'self'",
            "; frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}

