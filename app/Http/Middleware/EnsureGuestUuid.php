<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Requires a valid X-Guest-UUID header on public arcade routes. */
class EnsureGuestUuid
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid = $request->header('X-Guest-UUID');

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            return response()->json(['message' => 'A valid X-Guest-UUID header is required.'], 400);
        }

        $request->attributes->set('guest_uuid', strtolower($uuid));

        return $next($request);
    }
}
