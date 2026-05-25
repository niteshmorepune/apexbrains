<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalStudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('student')) {
            abort(403, 'Student access required.');
        }

        if ($user->student_type !== 'internal') {
            abort(403, 'This feature is available for enrolled students only.');
        }

        if (! $user->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
