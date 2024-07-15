<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Symfony\Component\HttpFoundation\Response;

class UserBelongsToOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        // $organizationId = $request->route('organization_id'); // Assuming organization_id is passed as a route parameter
        $organizationId = auth()->user()->organization_id; // Assuming organization_id is passed as a route parameter


        if (!$organizationId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization ID not provided'
            ], 400);
        }


        $organization = OrganizationModel::find($organizationId);

        if (!$organization) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found'
            ], 404);
        }

        // Check if the user is connected to this organization
        if (!$user->organizations()->where('organizations.id', $organizationId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not belong to the specified organization'
            ], 403);
        }

        return $next($request);
    }
}
