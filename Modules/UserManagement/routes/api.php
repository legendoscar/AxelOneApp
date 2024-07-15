<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\TenantOrgModule\App\Http\Controllers\OrganizationController;
use Modules\TenantOrgModule\App\Http\Controllers\FavoritesController;
use Modules\UserManagement\App\Http\Controllers\UserManagementController;
use Modules\UserManagement\App\Http\Controllers\RoleController;
use Modules\UserManagement\App\Http\Controllers\PermissionController;
use Modules\UserManagement\App\Http\Controllers\FacebookInterestsController;


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

// Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
//     Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
// });

// Route::middleware(['auth:api'])->prefix('v1')->name('api.')->group(function () {
//     // Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
//     Route::get('users','UserManagementController@index')->name('users.index');
// });


// foreach (config('tenancy.central_domains') as $domain) {
//     Route::domain($domain)->group(function () {

Route::group([
    'middleware' => ['api', 'jwt'],
    'prefix' => 'v1/users'
], function ($router) {
    // Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
    // Route::get('all', [UserManagementController::class, 'allUsers']);
    Route::get('all', [UserManagementController::class, 'getAllUsers'])->withoutMiddleware(['jwt']);

    // Route::post('users', [UserManagementController::class, 'store']);

    Route::get('profile', [UserManagementController::class, 'me']);
    // Route::get('{user}/profile/{profileUrl?}', [UserManagementController::class, 'me'])->name('users.profile');
    Route::post('profile', [UserManagementController::class, 'updateProfile']);
    Route::get('/interests', [UserManagementController::class, 'userIntersts']);
    Route::get('organizations/connected', [UserManagementController::class, 'connectedOrganizations']);

    Route::post('/organizations/logout', [UserManagementController::class, 'logoutBusiness']);
    Route::post('/organizations/{org_id}/switch', [UserManagementController::class, 'switchBusiness']);

    // Route::get('/interests', [FacebookInterestsController::class, 'fetchInterests']);


});


/* Search */
// Route::group([
//     'middleware' => ['api'],
//     'prefix' => 'v1/search'
// ], function ($router) {
//     Route::get('{query?}', [UserManagementController::class, 'searchUsers'])
//         ->where(['fields' => '.*'])
//         ->where(['query' => '.*']);

// });

/* Roles */
Route::group([
    'middleware' => ['api', 'jwt', 'email_verified'],
    'prefix' => 'v1/roles'
], function ($router) {
    Route::get('/', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/', [RoleController::class, 'store'])->name('roles.store');

    // Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    // Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    // Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    // Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

});

/* Roles */
Route::group([
    'middleware' => ['api', 'jwt', 'email_verified'],
    'prefix' => 'v1/permissions'
], function ($router) {
    Route::get('/', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/', [PermissionController::class, 'store'])->name('permissions.store');

    // Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    // Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    // Route::patch('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    // Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

});

// Route::group([
//     'middleware' => ['api', 'jwt', 'email_verified'],
//     'prefix' => 'v1/users'
// ], function ($router) {
//     // Route::get('usermanagement', fn (Request $request) => $request->user())->name('usermanagement');
//     // Route::get('all', [UserManagementController::class, 'allUsers']);
//     // // Route::post('users', [UserManagementController::class, 'store']);
//     // Route::get('{user}/profile', [UserManagementController::class, 'me']);

//     // Route::get('{user}/profile/{profileUrl?}', [UserManagementController::class, 'me'])->name('users.profile');
//     Route::post('profile', [UserManagementController::class, 'updateProfile']);
//     Route::get('/{user}', [UserManagementController::class, 'timeline'])->name('user.timeline');
//     Route::put('/tenants/switch', [UserManagementController::class, 'switchTenant']);
//     // ->middleware('user_belongs_to_organization');


// });

/* Likes */
Route::group([
    'middleware' => ['api', 'jwt', 'email_verified'],
    'prefix' => 'v1'
], function ($router) {

    Route::get('organizations/{id}/favorites', [FavoritesController::class, 'favoriteAnOrg']);
    Route::get('organizations/favorites', [FavoritesController::class, 'favoriteOrganizations']);
    // Route::get('/{id}', [OrganizationController::class, 'userOrgLike']);
});

/* Tenants/Orgs */
// Route::group([
//     'middleware' => ['api', 'jwt', 'email_verified'],
//     'prefix' => 'v1'
// ], function ($router) {

//     Route::put('/users/{user}/switch-tenant', [UserManagementController::class, 'switchTenant']);
//     // Route::get('/{id}', [OrganizationController::class, 'userOrgLike']);
// });


//     });
// }


