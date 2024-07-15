<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;



class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $permissions = Permission::all();
            return response()->json([
                'status' => 'success',
                'message' => count($permissions) . ' permissions fetched.',
                'data' => $permissions
            ], 200);
        } catch (QueryException $e) {
            // Log::error("An error occurred while fetching permissions: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => "An error occurred while fetching permissions: " . $e->getMessage() . " Please try again later."
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
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name|max:255',
            'guard_name' => 'nullable|max:255',
        ],
        [
            'unique' => 'The permission already exists'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input. Please check your input and try again.',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $permission = Permission::create($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'New permission created successfully',
                'data' => $permission
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            Log::error("An error occurred while creating a permission: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create a new permission. Please try again later.'
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
