<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    /**
     * Update the authenticated user's last_active_at timestamp.
     * Throttled to once per 5 minutes to avoid hammering the DB.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $lastActive = $user->last_active_at;

            // Only update if null or more than 5 minutes ago
            if (! $lastActive || $lastActive->diffInMinutes(now()) >= 5) {
                $user->timestamps = false; // Don't touch updated_at
                $user->last_active_at = now();
                $user->save();
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}
