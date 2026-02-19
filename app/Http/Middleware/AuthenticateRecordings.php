<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRecordings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priama kontrola session z request objektu
        $sessionData = $request->session()->all();

        $authenticated = (
            isset($sessionData['recordings_authenticated']) &&
            $sessionData['recordings_authenticated'] === true
        );

        if (!$authenticated) {
            return redirect()->route('recordings.login');
        }

        return $next($request);
    }
}

