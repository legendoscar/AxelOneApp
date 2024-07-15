<?php

use App\Http\Controllers\AuthController;
// use Modules\UserManagement\App\Http\Controllers\UserManagementController;
use Modules\UserManagement\App\Http\Controllers\Auth\UserRegistrationController;
use Modules\UserManagement\App\Http\Controllers\Auth\EmailVerificationController;
use Modules\UserManagement\App\Http\Controllers\Auth\UpdatePasswordController;
use Modules\UserManagement\App\Http\Controllers\Auth\ForgotPasswordController;
use Modules\UserManagement\App\Http\Controllers\Auth\ResetPasswordController;
use Modules\UserManagement\App\Http\Controllers\Auth\GeneratePasswordController;
use App\Http\Controllers\ClientRegistrationController;
use Modules\TenantOrgModule\App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Search\SearchController;
use Modules\UserManagement\App\Http\Controllers\UserManagementController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// foreach (config('tenancy.central_domains') as $domain) {
//     Route::domain($domain)->group(function () {
// your actual routes


Route::group([
    'middleware' => ['api', 'AddCorsHeader'],
    'prefix' => 'v1/auth'
], function ($router) {
    Route::post('users', [UserRegistrationController::class, 'registerUser'])->withoutMiddleware('email_verified');
    // Route::get('verify-email', [EmailVerificationController::class, 'verifyEmail']);
    // Route::get('resend-email-token', [EmailVerificationController::class, 'resendEmailToken']);
    // Route::put('update-password', [UpdatePasswordController::class, 'updatePassword']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['jwt', 'refresh.token']);
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware(['jwt']);
    // Route::post('me', [AuthController::class, 'me']);
});

Route::group([
    'middleware' => ['api', 'AddCorsHeader'],
    'prefix' => 'v1/auth/email'
], function ($router) {
    // Route::get('verify', [EmailVerificationController::class, 'verifyEmail']);
    Route::post('resend', [EmailVerificationController::class, 'resendEmailToken']);
    Route::post('newUser', [EmailVerificationController::class, 'sendNewUserOnboardingMessageWebhook']);


    Route::get('/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])
        // ->middleware(['signed'])
        ->name('verification.verify'); //sent to their email after registration
});

Route::group([
    'middleware' => ['api', 'AddCorsHeader'],
    'prefix' => 'v1/auth/password'
], function ($router) {
    Route::put('update', [UpdatePasswordController::class, 'updatePassword'])->middleware(['jwt', 'email_verified']);
    Route::post('forgot', [ForgotPasswordController::class, 'sendPasswordResetLink']);
    Route::post('reset', [ResetPasswordController::class, 'resetPassword'])->name('password.reset');
    Route::get('/generate', [GeneratePasswordController::class, 'generatePassword']);
});

Route::group([
    'middleware' => ['api', 'AddCorsHeader'],
    'prefix' => 'v1/auth'
], function ($router) {
    Route::post('register-client', [ClientRegistrationController::class, 'register']);
});


/* emails */
Route::get('email/verify/success', function () {
    return view('emails.verify-email.success', ['username' => 'Oscar']);
});

Route::get('/email/welcome', function () {
    return view('emails.welcome', ['username' => 'Oscar']);
});

Route::get('/email/matched', function () {
    return view('emails.business.matched', ['org_name' => 'Losintech LTD', 'url' => 'https://fyndah.com/businessDashboard/7/Naymer/search-request']);
});

Route::get('/email/business/new', function () {
    return view('emails.business.new-business', ['businessname' => 'Losintech LTD']);
});

Route::get('/email/password/reset_success', function () {
    return view('emails.password.password_reset_success', ['username' => 'Oscar']);
});

Route::get('/email/password/reset', function () {
    return view('emails.password.password_reset', ['username' => 'Oscar', 'url' => 'https://fyndah.com/businessDashboard/7/Naymer/search-request']);
});




/* Search */
// Route::group([
//     'middleware' => ['api'],
//     'prefix' => 'v1/search'
// ], function ($router) {
//     Route::get('{query?}', [SearchController::class, 'searchUsers'])
//         ->where(['fields' => '.*'])
//         ->where(['query' => '.*']);

// });

// /* Search Business Accounts */
// Route::group([
//     'middleware' => ['api', 'auth'],
//     'prefix' => 'v1/search'
// ], function ($router) {
//     Route::get('{query?}', [SearchController::class, 'searchBusiness'])
//         ->where(['fields' => '.*'])
//         ->where(['query' => '.*']);

// });


//     });
// }
