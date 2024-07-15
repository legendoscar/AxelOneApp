<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Modules\TenantOrgModule\App\Models\PostsModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Tenant;
use Illuminate\Support\Facades\DB;



class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orgs = PostsModel::orderBy('id', 'DESC')->paginate(100);
        return new JsonResponse($orgs, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createPost(Request $request)
    {
        // Retrieve the current authenticated user
        $user = User::find(Auth::id());

        // Retrieve the organization associated with the user
        // $organization = OrganizationModel::where('user_id', $user->id)->firstOrFail();

        // Validate the request data
        $data = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'organization_id' => 'required',
        ]);

        // Create a new post for the organization
        $post = new PostsModel;
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->user_id = $user->id;
        $post->organization_id = $data['organization_id'];
        $post->save();

        // Return a success response with the newly created post
        return response()->json(['message' => 'Post created successfully', 'data' => $post], 201);
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

    /**
     * Favorite/unfavorite an .
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
}
