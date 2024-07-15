<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login()
    {
        try {
            $validatedData = request()->validate(
                [
                    'email_or_username' => 'required|string',
                    'password' => 'bail|required|string'
                ]
            );

            $loginField = filter_var(request()->input('email_or_username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $credentials = request(['email_or_username', 'password']);
            $user = User::where($loginField, $credentials['email_or_username'])
                ->firstOrFail();

            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email not verified. Click on the resend verification link to verify your email!'
                ], 403);
            }

            if (Hash::check($credentials['password'], $user->password)) {
                $token = auth()->login($user);

                if ($token) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Successfully logged in',
                        'data' => $user->only(['id', 'firstname', 'lastname', 'username', 'email', 'organization_id', 'msg_id', 'profile_photo_path']),
                        'token' => $this->respondWithToken($token)
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Wrong email or password. Try again.'
            ], 401);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ], 200);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 6000 // This will be 0 if ttl is set to null
        ]);
    }
}
