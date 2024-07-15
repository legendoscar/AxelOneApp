<?php

// app/Http/Middleware/HandleRateLimit.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->status() == 429) {
            return response()->json([
                'message' => 'Too many requests. Please try again after a few minutes.'
            ], 429);
        }

        return $response;
    }
}
