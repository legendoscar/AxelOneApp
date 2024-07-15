<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SendPulseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\App\Rules\PasswordComplexity;


class UpdatePasswordController extends Controller
{
    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }
    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $validatedData = $request->validate([
            'old_password' => 'bail|required|string',
            'new_password' => 'required|string|confirmed|min:8',
        ],
    [
        'new_password.confirm' => 'The new passwords must match'
    ]);
        try {

            $user = auth()->user();

            if (!Hash::check($validatedData['old_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Old password is incorrect.'
                ], 401);
            }

            $user->password = Hash::make($validatedData['new_password']);

            if ($user->save()) {
                // Send the password reset confirmation email
                $htmlContent = view('emails.password.password_reset_success', ['username' => $user->username])->render();
                $this->sendPulseService->sendEmail($user->email, 'Password Reset Successful', $htmlContent);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully.'
                ], 200);
            } else {
                return response()->json(
                    [
                        'status' => 'failed',
                        'message' => 'Password update failed.'
                    ],
                    500
                );
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An unknown error occurred while updating the password. Try again',
                'code' => 500,
            ], 500);
        }
    }
}
