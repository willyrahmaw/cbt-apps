<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTimezoneFromSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tz = Setting::get('site_timezone', 'Asia/Jakarta');
            if ($tz && in_array($tz, timezone_identifiers_list())) {
                config(['app.timezone' => $tz]);
                date_default_timezone_set($tz);
            }
        } catch (\Throwable $e) {
            // Ignore if DB not ready (e.g. during migrations)
        }

        return $next($request);
    }
}
