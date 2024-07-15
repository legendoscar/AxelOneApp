<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided'
            ], 401);
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email not verified. Click on the resend verification link to verify your email!'
                ], 403);
            }

            $payload = JWTAuth::getPayload($token);
            $exp = $payload->get('exp');
            $now = time();
            $refreshThreshold = 300; // 5 minutes before expiration

            if ($exp - $now < $refreshThreshold) {
                $newToken = auth()->refresh();
                $response = $next($request);

                return $response->header('Authorization', 'Bearer ' . $newToken);
            }

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired. Please log in again'
            ], 403);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid. Please log in again'
            ], 403);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not valid. Please log in again'
            ], 401);
        }

        return $next($request);
    }
}
