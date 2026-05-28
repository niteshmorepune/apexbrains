<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FranchiseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('franchise.login');
        }

        if (! $user->hasRole('franchise_admin')) {
            abort(403, 'Franchise access required.');
        }

        if (! $user->franchise || $user->franchise->status !== 'active') {
            abort(403, 'Your franchise account is not active.');
        }

        return $next($request);
    }
}
