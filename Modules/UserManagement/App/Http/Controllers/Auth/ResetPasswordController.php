<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Services\SendPulseService;
use App\Models\User;


class ResetPasswordController extends Controller
{
    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }
    /**
     * Initiate Password Reset.
     */
    public function resetPassword(Request $request)
    {
        $validatedData = $request->validate(
            [
                'token' => 'required|string|regex:/^[a-zA-Z0-9]{40}$/|exists:users,password_token',
                'password' => 'bail|required|confirmed|string|min:8'
            ]
            ,
            [
                'token.regex' => 'The token is invalid or has been compromised',
                // 'token.unique' => 'Duplicate token found. Please request another',
                'token.exists' => 'Error. Token not Found',
            ]
        );
        try {

            $tokenCount = User::wherePasswordToken($validatedData['token'])->count();

            if ($tokenCount > 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Duplicate token found. Please request another.'
                ], 400);
            }

            $user = User::where('password_token', $validatedData['token'])
                ->where('password_token_expires_at', '>', now())
                ->firstOrFail();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token expired or invalid.',
                ], 404);
            }

            $user->password = Hash::make($validatedData['password']);
            $user->password_token = null;
            $user->password_token_expires_at = null;


            if ($user->save()) {

                // Send the password reset confirmation email
                $htmlContent = view('emails.password.password_reset_success', ['username' => $user->username])->render();
                $this->sendPulseService->sendEmail($user->email, 'Password Reset Successful', $htmlContent);

                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Password reset successful.'
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => 'failed',
                        'message' => 'Password reset failed.'
                    ],
                    500
                );
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An unknown error occurred while resetting the password. Try again',
                'code' => 500,
            ], 500);
        }
    }
}
