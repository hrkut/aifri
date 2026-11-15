<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('lroute')) {
    /**
     * URL of a public route in the current language: "program" becomes "en.program"
     * on English pages. Admin-only routes have no English variant and stay as they are.
     */
    function lroute(string $name, mixed $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $name = str_starts_with($name, 'en.') ? substr($name, 3) : $name;

        if ($locale === 'en' && Route::has('en.' . $name)) {
            $name = 'en.' . $name;
        }

        return route($name, $parameters, $absolute);
    }
}

if (! function_exists('locale_url')) {
    /**
     * The current public page in another language (for the SK/EN switcher and hreflang);
     * falls back to that language's home page.
     */
    function locale_url(string $locale): string
    {
        $route = request()->route();
        $name = $route?->getName();

        if ($name === null) {
            return lroute('home', [], true, $locale);
        }

        $base = str_starts_with($name, 'en.') ? substr($name, 3) : $name;
        $target = $locale === 'en' ? 'en.' . $base : $base;

        if (! Route::has($target)) {
            return lroute('home', [], true, $locale);
        }

        $query = request()->getQueryString();

        return route($target, $route->parameters()) . ($query ? '?' . $query : '');
    }
}
