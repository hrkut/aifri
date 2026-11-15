<?php

namespace App\Http\Middleware;

use App\Models\Conference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets admins work with an edition other than the publicly active one
 * (selected on the "Ročníky" page and remembered in the session).
 */
class UseAdminConference
{
    public const SESSION_KEY = 'admin_conference_id';

    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get(self::SESSION_KEY);

        if ($id !== null) {
            $conference = Conference::query()->find($id);

            if ($conference) {
                Conference::setCurrent($conference);
            } else {
                $request->session()->forget(self::SESSION_KEY);
            }
        }

        return $next($request);
    }
}
