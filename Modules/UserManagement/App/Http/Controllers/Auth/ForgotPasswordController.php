<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SendPulseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }
    public function sendPasswordResetLink(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ],
    [
        'email.exists' => 'Email not found. Try again.'
    ]);

        $user = User::where('email', $validatedData['email'])->firstOrFail();

        if ($user->password_token && $user->password_token_expires_at && $user->password_token_expires_at->isFuture()) {
            $token = $user->password_token;
            $expiresAt = $user->password_token_expires_at;

        } else {
            $token = Str::random(40);
            $user->update([
                'password_token' => $token,
                'password_token_expires_at' => Carbon::now()->addMinutes(30),
            ]);
            $expiresAt = $user->password_token_expires_at;

        }

        // return $token;

        $resetUrl = "https://fyndah.com/ResetPassword?token=$token";
        $htmlContent = view('emails.password.password_reset', ['url' => $resetUrl, 'username' => $user->username])->render();

        // Send the password reset email
        $this->sendPulseService->sendEmail($user->email, 'Password Reset', $htmlContent);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset token sent successfully.',
            'expiresAt' => $expiresAt
        ], 200);
    }
}
