<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRecordingsGlobal
{
    /**
     * Handle an incoming request.
     * Tento middleware sa spúšťa PRED routingom, takže zachytá všetky
     * requesty na /zaznamy/record a /zaznamy/presentation
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Kontroluj či request ide na /zaznamy/record alebo /zaznamy/presentation
        if (preg_match('#^zaznamy/(record|presentation)/#', $path)) {
            $authenticated = false;

            try {
                // Debug logging
                $sessionVal = session('recordings_authenticated');
                \Illuminate\Support\Facades\Log::info('AuthRecordingsGlobal', [
                    'path' => $path,
                    'session_value' => $sessionVal,
                    'is_true' => $sessionVal === true,
                ]);

                if (session('recordings_authenticated') === true) {
                    $authenticated = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('AuthRecordingsGlobal Error', [
                    'error' => $e->getMessage(),
                    'path' => $path,
                ]);
            }

            if (!$authenticated) {
                return redirect()->route('recordings.login');
            }
        }

        return $next($request);
    }
}

