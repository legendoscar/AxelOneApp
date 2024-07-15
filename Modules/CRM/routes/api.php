<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\CRM\App\Http\Controllers\OrganizationController;


/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

// Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
//     Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
// });

// Route::middleware(['auth:api'])->prefix('v1')->name('api.')->group(function () {
//     // Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
//     Route::get('users','UserManagementController@index')->name('users.index');
// });

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    // Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
    Route::get('orgs', [OrganizationController::class, 'index']);
    // Route::post('users', [UserManagementController::class, 'store']);
    // Route::get('verify/{token}', [UserManagementController::class, 'verify'])->name('verification.verify');

    // Route::get('email/verify/{id}', [UserManagementController::class, 'verifyEmail'])->name('verification.verify');
    // Route::get('email/resend', [UserManagementController::class, 'resendEmail'])->name('verification.resend');
    // Route::post('me', [AuthController::class, 'me']);

});


