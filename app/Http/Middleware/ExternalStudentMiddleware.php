<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExternalStudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('student')) {
            abort(403, 'Student access required.');
        }

        if ($user->student_type !== 'external') {
            abort(403, 'This area is for competition-registered students only.');
        }

        if (! $user->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
