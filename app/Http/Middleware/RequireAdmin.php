<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Requires the authenticated user to have role = admin or editor.
// Must be used after the 'auth' middleware (which handles the login redirect).
class RequireAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isEditor()) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
