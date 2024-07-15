<?php

namespace Modules\TenantOrgModule\App\Http\Controllers\OrgProfiles;

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



class OrgLocationsController extends Controller
{

    /**
     * Fetch Org Locations.
     */
    public function fetchOrgLocations()
    {

        return 33;

        // try {
            return $org_key = auth()->user();

            // First, try to find an organization by ID
            return $org = OrganizationModel::with('locations')->findOrFail($org_key);

            // If no organization is found, try to find by name
            // if (!$org) {
            //     $org = OrganizationModel::with('locations')
            //     ->where('org_name', $org_key)
            //         // ->orWhere('subdomain', $org_key)
            //         ->firstOrFail();
            // }

            // $location = $org->location;

            return response()->json([
                'status' => 'success',
                'message' => is_null($org) ? '0 locations found' : count($org) . ' locations found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'locations' => $org
                    ]
                ]
            ], 200);

        // } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Organization not found',
            // ], 404);
        // }
    }

}
