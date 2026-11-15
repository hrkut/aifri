<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the language of the public site from the route group: Slovak without a
 * prefix, English under /en (see routes/web.php).
 */
class SetLocale
{
    public const SUPPORTED = ['sk', 'en'];

    public function handle(Request $request, Closure $next, string $locale = 'sk'): Response
    {
        $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'sk';

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
