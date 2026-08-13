<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isOwner()) {
            abort(403, 'Only the shop owner/admin can access this area.');
        }

        return $next($request);
    }
}