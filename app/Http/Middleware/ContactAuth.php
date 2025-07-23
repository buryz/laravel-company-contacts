<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ContactAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Wymagane uwierzytelnienie.',
                    'error' => 'Unauthenticated'
                ], 401);
            }

            return redirect()->route('auth.login')
                ->with('error', 'Musisz być zalogowany, aby uzyskać dostęp do tej strony.');
        }

        return $next($request);
    }
}