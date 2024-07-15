<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class RefreshTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            if (!auth()->check()) {
                return $next($request);
            }

            $token = JWTAuth::getToken();
            if (!$token) {
                return $next($request);
            }

            $payload = JWTAuth::getPayload($token);
            $exp = $payload->get('exp');
            $now = time();
            $refreshThreshold = 300; // 5 minutes before expiration

            if ($exp - $now < $refreshThreshold) {
                $newToken = JWTAuth::refresh($token);
                auth()->setToken($newToken);
                $response = $next($request);
                $response->headers->set('Authorization', 'Bearer ' . $newToken);
                return $response;
            }
        } catch (TokenExpiredException $e) {
            try {
                $newToken = JWTAuth::refresh(JWTAuth::getToken());
                auth()->setToken($newToken);
                $response = $next($request);
                $response->headers->set('Authorization', 'Bearer ' . $newToken);
                return $response;
            } catch (JWTException $ex) {
                return response()->json(['error' => 'Token could not be refreshed'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token error'], 401);
        }

        return $next($request);
    }
}
