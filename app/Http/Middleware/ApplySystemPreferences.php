<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySystemPreferences
{
    /**
     * Applies the "General Settings" / "Security Settings" preferences that
     * need to take effect on every request, rather than only on the
     * Settings page itself:
     *   - Default Language  -> App/Carbon locale
     *   - Time Zone         -> PHP + Carbon default timezone
     *   - Auto Logout       -> session lifetime
     *
     * Runs before session/locale are resolved further down the middleware
     * stack, so these values are already in place by the time anything
     * else reads them.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Setting::get('default_language');
        if ($locale && in_array($locale, ['en', 'ne'], true)) {
            app()->setLocale($locale);
            \Carbon\Carbon::setLocale($locale);
        }

        $timezone = Setting::get('app_timezone');
        if ($timezone && in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);
        }

        $autoLogoutMinutes = Setting::get('auto_logout_minutes');
        if ($autoLogoutMinutes && (int) $autoLogoutMinutes > 0) {
            config(['session.lifetime' => (int) $autoLogoutMinutes]);
        }

        return $next($request);
    }
}