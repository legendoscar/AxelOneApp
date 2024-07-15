<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\SendPulseService;
use Log;
use Modules\UserManagement\App\Http\Controllers\Auth\NewUserWebhookController;



class EmailVerificationController extends Controller
{
    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }


    /**
     * Verify email with token.
     */
    // public function verifyEmail(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'token' => 'required|string|regex:/^[a-zA-Z0-9]{60}$/',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors()->first(),
    //         ], 400);
    //     }

    //     $user = User::where('email_token', $request->token)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'User not found or token expired. Please try again or request a new email verification token.',
    //         ], 404);
    //     }

    //     // Check if the token is older than 6 hours
    //     if ($user->email_token_created_at->diffInHours(now()) >= 6) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Token expired. Please request a new email verification token.',
    //         ], 401);
    //     }

    //     if ($user->email_verified_at) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Email already verified.',
    //         ], 403);
    //     }

    //     if ($user->update(['email_verified_at' => now(), 'email_token' => null, 'email_token_created_at' => null])) {

    //         // Trigger webhook
    //         $this->sendNewUserOnboardingMessageWebhook($user);

    //         $url = "https://fyndah.com/";
    //         $htmlContent = view('emails.verify-email.success', ['url' => $url, 'username' => $user->username])->render();

    //         // send verification success email
    //         $this->sendPulseService->sendEmail($user->email, 'Email Verification Successful', $htmlContent);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Email verified successfully.',
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Unable to verify email. Please try again or request a new email verification token.',
    //         ], 401);
    //     }

    // }

    /**
     * Resend email verification token.
     */

    public function resendEmailToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|exists:users,email',
            ], [
                'email.exists' => 'Error. Email not found.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email already verified.'
                ], 403);
            }

            // Check if the token is still valid
            if ($user->email_token_created_at && $user->email_token_created_at->diffInHours(now()) < 6) {
                // Use the existing valid token
                $token = $user->email_token;
                $expiresAt = $user->email_token_created_at;
            } else {
                // Generate a new token if the current one is invalid or expired
                $token = Str::random(60);
                $user->update([
                    'email_token' => $token,
                    'email_token_created_at' => now()
                ]);
                $expiresAt = $user->email_token_created_at;
            }

            $verificationUrl = route('verification.verify', ['id' => $user->id, 'token' => $token]);
            $htmlContent = view('emails.verify-email.verify', ['url' => $verificationUrl, 'username' => $user->username])->render();

            // send verification email
            $this->sendPulseService->sendEmail($user->email, 'Email Verification', $htmlContent);

            return response()->json([
                'status' => 'success',
                'message' => "Success! Please check your email to verify your account on Fyndah",
                'expiresAt' => $expiresAt
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while sending the verification email: ' . $e->getMessage()
            ], 500);
        }
    }




    public function verify(Request $request)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($request->route('id'));

            // Check if the token matches the user's email_token
            if ($user->email_token !== $request->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token provided.',
                ], 401);
            }

            // Check if the token is older than 6 hours
            if ($user->email_token_created_at->diffInHours(now()) >= 6) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token expired. Please request a new email verification token.',
                ], 401);
            }

            // Check if email is already verified
            // if ($user->hasVerifiedEmail()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Email already verified.',
            //     ], 403);
            // }

            // // Mark email as verified
            // if ($user->markEmailAsVerified()) {
            //     $user->update(['email_token' => null, 'email_token_created_at' => null]);

            //     $url = "https://fyndah.com/";
            //     $htmlContent = view('emails.verify-email.success', ['url' => $url, 'username' => $user->username])->render();

            //     // send verification success email
            //     $this->sendPulseService->sendEmail($user->email, 'Email Verification Successful', $htmlContent);

            //     // Prepare and send welcome email
            //     $htmlContent = view('emails.welcome', ['username' => $user->username])->render();
            //     $this->sendPulseService->sendEmail($user->email, 'Welcome to Fyndah - The New Way To Discover Local Businesses!', $htmlContent);

            //     // Trigger webhook
            //     $this->sendNewUserOnboardingMessageWebhook($user);

            //     // Redirect user after successful verification
            //     return redirect()->away('https://fyndah.com/login')->with('success', 'Email verified successfully. Check your inbox for our Welcome Message');
            // } else {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Unable to verify email. Please try again or request a new email verification token.',
            //     ], 401);
            // }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error verifying email: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            // Return a generic error response
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while verifying email. Please try again later.',
            ], 500);
        }
    }


    public function sendNewUserOnboardingMessageWebhook($user)
    {
        //the webhook payload
        $payload = [
            'username' => $user->username,
            'msg_id' => $user->msg_id,
        ];

        // return $payload;


        try {
            $webhookUrl = 'https://axelonepostfeature.onrender.com/api/messages/webhook/user-registered';
            $response = Http::withHeaders([
                'Content-type' => 'application/json'
            ])->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Webhook sent successfully', [
                    'user' => [
                        'username' => $user->username,
                        'msg_id' => $user->msg_id,
                    ]
                ]);
            } else {
                Log::error('Failed to send webhook notification to user', [
                    'user' => [
                        'username' => $user->username,
                        'msg_id' => $user->msg_id,
                    ],
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending webhook notification to user', [
                'user' => [
                    'username' => $user->username,
                    'msg_id' => $user->msg_id,
                ],
                'exception_message' => $e->getMessage()
            ]);
        }
    }
}
