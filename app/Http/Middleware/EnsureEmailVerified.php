<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class EnsureEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is valid and logged in
        // $response = $request->route()->middleware('jwt');

        // // If the JWT middleware returns a non-null response, return it immediately
        // if (!is_null($response)) {
        //     return $response;
        // }
        $user = auth()->user();

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email verification is required',
            ], 401);
        }

        return $next($request);
    }
}
