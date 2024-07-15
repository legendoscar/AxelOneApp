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



class OrgUpdateProfileController extends Controller
{

    /**
     * Update Org Locations.
     */
    public function updateOrgLocations($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            // return $org->location->get('city');
            // $street_addr = request()->location['street_addr'];
            // $city = request()->location['city'];
            // $lga = request()->location['lga'];
            // $state = request()->location['state'];
            // $country = request()->location['country'];

            // $org->location = !is_null($org->location) ? array_replace_recursive(
            //     $org->location,
            //     [
            //         'street_addr' => request()->location['street_addr'],
            //         'city' => request()->location['city'],
            //         'lga' => request()->location['lga'],
            //         'state' => request()->location['state'],
            //         'country' => request()->location['country'],
            //     ]
            // ) : '';

            // Merge existing location with new data
            // $org->location = array_merge($org->location ?? [], request()->all());

            $location = $org->location ?? [];
            // return request()->all();

            foreach (request()->all() as $key => $value) {
                if (isset($location[$key])) {
                    $location[$key] = $value;
                }
            }

            // $org->location = request()->location;
             $org->location = $location;


            $org->save();

            return response()->json([
                'status' => 'success',
                // 'message' => is_null($org->location) ? '0 locations found' : count($org->location) . ' locations found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'locations' => $org->location
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

}
