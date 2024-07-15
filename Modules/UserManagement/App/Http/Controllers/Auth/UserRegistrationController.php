<?php

namespace Modules\UserManagement\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;
use Illuminate\Support\Str;
use Modules\UserManagement\App\Rules\PasswordComplexity;
use App\Services\SendPulseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;



class UserRegistrationController extends Controller
{

    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }
    /**
     * Display a listing of the resource.
     */
    public function registerUser(Request $request)
    {

        DB::beginTransaction();
        $validationRules = [
            'firstname' => 'bail|string|max:255',
            'lastname' => 'bail|string|max:255',
            'username' => 'bail|required|string|min:5|max:255|unique:users|regex:/^\S*$/u',
            'email' => 'bail|required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|bail|string|unique:users|max:255|phone:NG,GB,US,CA,AU',
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'confirmed',
                // new PasswordComplexity($request->username, $request->email)
            ],
        ];

        $validator = Validator::make($request->all(), $validationRules, [
            'username.regex' => 'The username field must not contain spaces.',
            'username.unique' => 'The username already exists or has been taken',
            'email.unique' => 'The email already exists or has been taken',
            'phone_number.phone' => 'Only Nigerian, Canadian, US, Australian and UK Phone Numbers are accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all(),
            ], 400);
        }
        try {


            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'profile_url' => $request->username,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);

            // Generate email verification token
            $verification_token = Str::random(60);
            $user->update(['email_token' => $verification_token]);

            DB::commit();

            // $user->sendEmailVerificationNotification();
            $verificationUrl = route('verification.verify', ['id' => $user->id, 'token' => $user->email_token]);

            $htmlContent = view('emails.verify-email.verify', ['url' => $verificationUrl, 'username' => $user->username])->render();

            $this->sendPulseService->sendEmail($user->email, 'Email Verification', $htmlContent);

            return response()->json([
                'status' => 'success',
                // 'data' => $user,
                // 'message' => 'Account created successfully. Use the generated token for email verification.',
                // 'message' => "Please verify your email by clicking on the following link: <a href=\"$verificationUrl\">Verify Email</a>"
                'message' => "Please check your email to verify your account on Fyndah"
            ], 201);
        } catch (\Exception $e) {
            // Return a JSON response with an error message
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the user: ' . $e->getMessage()
            ], 500);
        }
    }
}
