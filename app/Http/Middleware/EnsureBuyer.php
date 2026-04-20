<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyer
{
    /**
     * Handle an incoming request.
     *
     * Buyer access in this app means "any authenticated user who can shop",
     * which includes vendor users (a user can be both buyer and vendor).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            abort(403);
        }

        return $next($request);
    }
}
