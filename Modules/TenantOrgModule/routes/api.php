<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\TenantOrgModule\App\Http\Controllers\OrganizationController;
use Modules\TenantOrgModule\App\Http\Controllers\OrgFetchProfileController;
use Modules\TenantOrgModule\App\Http\Controllers\OrgUpdateProfileController;
use Modules\TenantOrgModule\App\Http\Controllers\WalletController;
use Modules\TenantOrgModule\App\Http\Controllers\FlutterwaveController;
use Modules\TenantOrgModule\App\Http\Controllers\BusinessCategoryController;
use Modules\TenantOrgModule\App\Http\Controllers\BusinessSubUnitController;
use Modules\TenantOrgModule\App\Http\Controllers\OrgProfiles\OrgLocationsController;
use Modules\TenantOrgModule\App\Http\Controllers\UserInvitationController;
use Modules\TenantOrgModule\App\Http\Controllers\PaystackController;





// use App\Http\Controllers\


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



/* organizations */
Route::group([
    'middleware' => ['api', 'jwt', 'AddCorsHeader', 'email_verified'],
    'prefix' => 'v1/organization'
], function ($router) {
    Route::post('/create', [OrganizationController::class, 'createTenantOrg']);
    Route::post('/update', [OrganizationController::class, 'updateOrganization'])->middleware('check.user.belongs.to.any.org');
});

Route::get('/v1/organization/featured', [OrganizationController::class, 'featuredorganizations']);


Route::group([
    'middleware' => ['api'],
    'prefix' => 'v1/organization'
], function ($router) {
    Route::get('/locations/{org_key}', [OrgFetchProfileController::class, 'fetchOrgLocations']);
    Route::put('/{org_key}/locations', [OrgUpdateProfileController::class, 'updateOrgLocations']);

    // Route::get('/products/{org_key}', [OrgFetchProfileController::class, 'fetchOrgProducts']);

    // Route::get('/services/{org_key}', [OrgFetchProfileController::class, 'fetchOrgServices']);

    // Route::get('/business-hours/{org_key}', [OrgFetchProfileController::class, 'fetchOrgBusinessHours']);

    // Route::get('/social-media/{org_key}', [OrgFetchProfileController::class, 'fetchOrgSocialMedia']);

    // Route::get('/contact-info/{org_key}', [OrgFetchProfileController::class, 'fetchOrgContactinfo']);

    // Route::get('/reviews-ratings/{org_key}', [OrgFetchProfileController::class, 'fetchOrgReviewsRatings']);

    // Route::get('/pricing/{org_key}', [OrgFetchProfileController::class, 'fetchOrgPricing']);

    // Route::get('/certifications/{org_key}', [OrgFetchProfileController::class, 'fetchOrgCertifications']);

    // Route::get('/languages/{org_key}', [OrgFetchProfileController::class, 'fetchOrgLanguages']);

    // Route::get('/payment-methods/{org_key}', [OrgFetchProfileController::class, 'fetchOrgPaymentMethods']);

    // Route::get('/nearby-landmarks/{org_key}', [OrgFetchProfileController::class, 'fetchOrgNearbyLandmarks']);

    // Route::get('/parking-info/{org_key}', [OrgFetchProfileController::class, 'fetchOrgParkinginfo']);

    // Route::get('/pet-policy/{org_key}', [OrgFetchProfileController::class, 'fetchOrgPetPolicy']);

    // Route::get('/dress-code/{org_key}', [OrgFetchProfileController::class, 'fetchOrgDressCode']);

    // Route::get('/special_instr/{org_key}', [OrgFetchProfileController::class, 'fetchOrgSpecialInstructions']);

    // Route::get('/accessibility/{org_key}', [OrgFetchProfileController::class, 'fetchOrgAccessibility']);

    // Route::get('/events/{org_key}', [OrgFetchProfileController::class, 'fetchOrgEvents']);

    // Route::get('/cancellation/{org_key}', [OrgFetchProfileController::class, 'fetchOrgCancellation']);

    // Route::get('/environmental/{org_key}', [OrgFetchProfileController::class, 'fetchOrgEnvironmental']);

    // Route::get('/awards/{org_key}', [OrgFetchProfileController::class, 'fetchOrgAwards']);

    // Route::get('/user-generated/{org_key}', [OrgFetchProfileController::class, 'fetchOrgUserGeneratedContents']);

    Route::get('/', [OrganizationController::class, 'index']);
    Route::post('/check-user-org', [OrganizationController::class, 'checkUserBelongsToOrganization']);

    // /* categories */
    Route::get('/categories', [BusinessCategoryController::class, 'index']);
    Route::get('/categories/{id}', [BusinessCategoryController::class, 'show']);

    Route::post('/{id}/attach-cat-subcat', [OrganizationController::class, 'attachCategoriesAndSubCategories']);

    /* sub categories */
    Route::get('/sub-categories', [BusinessSubUnitController::class, 'index']);
    Route::get('/sub-categories/{id}', [BusinessSubUnitController::class, 'show']);

    Route::get('/locations', [OrgLocationsController::class, 'fetchOrgLocations']);


    Route::get('/{org_id}/users', [OrganizationController::class, 'connectedUsers']);

    Route::get('/{org_key}', [OrgFetchProfileController::class, 'fetchOrgProfile'])->withoutMiddleware('email_verified');


});

Route::group([
    'middleware' => ['api', 'AddCorsHeader', 'jwt', 'check.user.belongs.to.any.org'],
    'prefix' => 'v1/organization'
], function ($router) {
    // Route::post('/wallet/deposit', [WalletController::class, 'deposit']);

    Route::post('flutterwave/pay', [FlutterwaveController::class, 'initiatePayment'])
        ->middleware('jwt', 'email_verified', 'check.user.belongs.to.any.org', 'handle.rate.limit', 'throttle:60,1');

    Route::get('flutterwave/webhook', [FlutterwaveController::class, 'handleWebhook'])
        ->middleware('email_verified');
    // ->middleware('handle.rate.limit');

    Route::post('flutterwave/callback', [FlutterwaveController::class, 'handleCallback'])
        ->middleware('handle.rate.limit');

    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);

    Route::post('/wallet/balance', [WalletController::class, 'balance']);

    Route::post('/wallet/balance_at_date', [WalletController::class, 'balanceAtDate']);

    Route::post('/wallet/transactions', [WalletController::class, 'transactions']);

    Route::post('/wallet/all_transactions', [WalletController::class, 'allTransactions']);

    Route::post('paystack/pay', [PaystackController::class, 'initializePayment'])
        ->middleware('handle.rate.limit', 'throttle:60,1');

    Route::get('paystack/callback', [PaystackController::class, 'verifyPayment'])
        ->withoutMiddleware(['jwt', 'check.user.belongs.to.any.org'])->middleware('handle.rate.limit')->name('paystack.callback');

});


Route::group([
    'middleware' => ['api', 'jwt'],
    'prefix' => 'v1/organization'
], function ($router) {
    // Route::get('/lations', [OrgLocationsController::class, 'fetchOrgLocations']);

});


Route::group([
    'middleware' => ['api', 'jwt'],
    'prefix' => 'v1/organization'
], function ($router) {
    /* user invitations  */
    Route::post('/invitations', [UserInvitationController::class, 'sendInvitations'])->middleware('check.user.belongs.to.any.org');
    Route::post('/invitations/accept', [UserInvitationController::class, 'acceptInvitation']);
    Route::post('/invitations/decline', [UserInvitationController::class, 'declineInvitation']);
    Route::get('/invitations', [UserInvitationController::class, 'getOrganizationInvitationsHistory']);
    Route::get('/joined-users', [UserInvitationController::class, 'getUsersJoinedViaInvitations']);


    Route::get('/user/invitations', [UserInvitationController::class, 'getUserInvitationHistory']);
    Route::get('/user/joined-organizations', [UserInvitationController::class, 'getUserJoinedOrganizations']);
    Route::get('/user/declined-organizations', [UserInvitationController::class, 'getUserDeclinedOrganizations']);

});

