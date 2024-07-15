<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Tenant;
use Illuminate\Support\Facades\DB;



class FavoritesController extends Controller
{
    /**
     * Favorite/unfavorite an org.
     */
    public function favoriteAnOrg($id)
    {
        $user = auth()->user();
        $org = OrganizationModel::findOrFail($id);

        $user->toggleFavorite($org);

        $message = $user->hasFavorited($org) == 1 ? 'Organization liked' : 'Organization unliked';
        // return $user->getFavoriteItems(OrganizationModel::class)->get();


        return response()->json([
            'message' => $message,
            'data' => [
                'organization' => $org,
                'user' => $user,
            ],
        ], 201);
    }

    /**
     * Fetch list of organizations favorited by user.
     */
    public function favoriteOrganizations()
    {
        $user = auth()->user();
        $favoriteBusinesses = $user->getFavoriteItems(OrganizationModel::class)->paginate(50, ['id', 'org_name']);

        return response()->json([
            'message' => 'Success. ' . count($favoriteBusinesses) . ' businesses favorited',
            'data' => $favoriteBusinesses,
        ], 200);
    }
}
