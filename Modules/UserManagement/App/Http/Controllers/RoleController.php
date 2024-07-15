<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $roles = Role::all();
            return response()->json([
                'status' => 'success',
                'message' => 'All roles fetched successfully',
                'data' => $roles
            ], 200);
        } catch (QueryException $e) {
            Log::error("An error occurred while fetching roles: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch roles. Please try again later.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('usermanagement::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    // : Response
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|unique:roles,name|max:255',
                // 'guard_name' => 'nullable|max:255',
                'permissions' => 'array',
            ],
            [
                'unique' => 'The role already exists'
            ]
        );

        // return $validator->errors();

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input. Please check your input and try again.',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $role = Role::create(['name' => $request->name]);
            $role->syncPermissions($request->input('permissions', []));
            return response()->json([
                'status' => 'success',
                'message' => 'New role created successfully with synced permissions',
                'data' => $role
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            Log::error("An error occurred while creating a role: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create a new role and sync permissions. Please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('usermanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('usermanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
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
