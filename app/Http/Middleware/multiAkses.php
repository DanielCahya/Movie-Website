<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class multiAkses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles  Roles that are permitted to pass
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|never
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Check if the user's role matches any of the specified roles or is "superadmin"
        if (in_array($userRole, $roles, true) || $userRole === 'superadmin') {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
