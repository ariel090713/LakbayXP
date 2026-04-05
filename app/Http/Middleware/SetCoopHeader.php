<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCoopHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Allow Firebase Google popup to communicate back to the opener window
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        return $response;
    }
}
