<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\LeadsManagementModule\App\Http\Controllers\SearchController;
use Modules\LeadsManagementModule\App\Http\Controllers\BidsController;
use Modules\LeadsManagementModule\App\Http\Controllers\LocationController;
use Modules\LeadsManagementModule\App\Http\Controllers\SearchRequestsController;
use Modules\LeadsManagementModule\App\Http\Controllers\LeadsController;

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


/* Search Business Accounts */
Route::group([
    'middleware' => ['api', 'jwt', 'refresh.token', 'AddCorsHeader'],
    'prefix' => 'v1/search'
], function ($router) {
    Route::get('/requests/user', [SearchRequestsController::class, 'getUserSearchRequests'])->middleware('check.user.belongs.to.any.org');
    Route::get('/requests/active', [SearchRequestsController::class, 'activeSearchRequests'])->middleware('check.user.belongs.to.any.org');
    Route::get('/requests/history', [SearchRequestsController::class, 'getSearchRequestsHistory'])->middleware('check.user.belongs.to.any.org');

    Route::get('/requests/{id}', [SearchRequestsController::class, 'fetchSearchRequestById'])->middleware('check.user.belongs.to.any.org');
    Route::get('/requests/{id}/bids', [SearchRequestsController::class, 'getOrganizationsThatBidOnTheSearchRequest'])->middleware('check.user.belongs.to.any.org');
    Route::post('/requests/{bid_id}/bid', [BidsController::class, 'placeBid'])->middleware('check.user.belongs.to.any.org');

    Route::post('/business', [SearchController::class, 'search']);

});

Route::group([
    'middleware' => ['api', 'jwt'],
    'prefix' => 'v1/bids'
], function ($router) {

    Route::get('/history', [BidsController::class, 'getOrganizationBidsHistory'])->middleware('check.user.belongs.to.any.org');
    Route::get('/active', [BidsController::class, 'activeOrganizationBids'])->middleware('check.user.belongs.to.any.org');
    Route::get('/lost', [BidsController::class, 'getBidsLostByOrganization'])->middleware('check.user.belongs.to.any.org');
    Route::get('/won', [BidsController::class, 'getBidsWonByOrganization'])->middleware('check.user.belongs.to.any.org');

    Route::post('/place', [BidsController::class, 'placeBid'])->middleware('check.user.belongs.to.any.org');


    Route::post('/{id}/close', [BidsController::class, 'closeActiveBid'])->middleware('check.user.belongs.to.any.org');
    Route::get('/{id}/status', [BidsController::class, 'getBidStatus'])->middleware('check.user.belongs.to.any.org');
    Route::get('/{id}', [BidsController::class, 'getBid'])->middleware('check.user.belongs.to.any.org');
    Route::put('/{id}', [BidsController::class, 'updateBid'])->middleware('check.user.belongs.to.any.org');
    Route::delete('/{id}', [BidsController::class, 'deleteBid'])->middleware('check.user.belongs.to.any.org');


});

Route::group([
    'middleware' => ['api', 'jwt', 'check.user.belongs.to.any.org'],
    'prefix' => 'v1/leads'
], function ($router) {
    // Route::post('/{lead_id}/bids', [BidsController::class, 'placeBid']);
    // Fetch leads belonging to an organization
    Route::get('/', [LeadsController::class, 'fetchLeadsByOrg']);

    // Fetch leads of which a particular user made the search
    Route::get('leads/user/{userId}', [LeadsController::class, 'fetchLeadsByUser']);

    // Marking a lead with various status labels using the laravel-model-status
    Route::post('leads/{id}/status', [LeadsController::class, 'markLeadStatus']);

    // Delete a lead
    Route::delete('leads/{id}', [LeadsController::class, 'deleteLead']);

    // Assign a lead to another user who belongs to the organization
    Route::post('leads/{id}/assign', [LeadsController::class, 'assignLeadToUser']);

});

Route::group([
    'middleware' => ['api'],
    'prefix' => 'v1/locations'
], function ($router) {
    // Route::get('/get-token', [LocationController::class, 'getToken']);
    Route::get('/countries', [LocationController::class, 'getCountries']);
    Route::get('/states/{country}', [LocationController::class, 'getStates']);
    Route::get('/cities/{country}/{state}', [LocationController::class, 'getCities']);

});

Route::get('v1/pusher', function () {
    return view('pusher');
});
