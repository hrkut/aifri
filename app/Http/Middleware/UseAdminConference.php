<?php

namespace App\Http\Middleware;

use App\Models\Conference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets admins work with an edition other than the publicly active one
 * (selected on the "Ročníky" page and remembered in the session).
 * Archived editions (see Conference::isReadOnly()) are read-only.
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

        $conference = Conference::current();

        if ($conference->isReadOnly() && $this->isWriteRequest($request)) {
            $message = "Ročník {$conference->year} je archivovaný – dá sa len prezerať.";

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'error' => $message], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        return $next($request);
    }

    /** Any change of data, or a page that only exists to change data (create/edit forms). */
    private function isWriteRequest(Request $request): bool
    {
        // Managing editions themselves (switching, publishing, editing dates) is always allowed.
        if ($request->routeIs('admin.conferences.*')) {
            return false;
        }

        if (! $request->isMethodSafe()) {
            return true;
        }

        return $request->routeIs(self::FORM_ROUTES);
    }

    private const FORM_ROUTES = [
        'admin.program.create',
        'admin.program.duration',
        'admin.breaks.create',
        'admin.breaks.edit',
        'admin.registration.edit',
    ];
}
