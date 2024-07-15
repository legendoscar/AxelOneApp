<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\App\Models\OrganizationModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Tenant;



class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orgs = OrganizationModel::orderBy('id', 'DESC')->paginate(10);
        return new JsonResponse($orgs, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createOrganization(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'organization_name' => 'required',
            // Add more validation rules as needed
        ]);

        // Create the new tenant (organization)
        $tenant = Tenant::create([
            'name' => $validatedData['organization_name'],
            // Add more tenant attributes as needed
        ]);

        // Retrieve the authenticated user (creator)
        $user = User::find(Auth::user()->id);

        // Associate the creator user with the new tenant
        $user->tenant_id = $tenant->id;
        $user->save();

        // Optionally, you may want to create a new user for the creator within the tenant's user table
        $tenantUser = $tenant->users()->create([
            'name' => $user->name,
            'email' => $user->email,
            // Add more user attributes as needed
        ]);

        return response()->json([
            'message' => 'Organization created successfully',
            'data' => [
                'tenant' => $tenant,
                'creator_user' => $user,
                // Add more data as needed
            ],
        ], 201);
    }


    /**
     * Store a newly created resource in storage.
     */


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('crm::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


}
