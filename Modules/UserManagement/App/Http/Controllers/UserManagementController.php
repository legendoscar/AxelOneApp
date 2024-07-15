<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Notification;

use App\Models\User;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Validator;
use Illuminate\Support\Str;
use Auth;
use Modules\UserManagement\App\Notifications\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Carbon\Carbon;
use App\Http\Middleware\ValidateRegistrationDataMiddleware;
// use Spatie\Searchable\Search;
use App\Services\Search;
use Modules\UserManagement\App\Models\UserActivityHistoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class UserManagementController extends Controller
{
    /**i
     * Display a listing of the resource.
     */
    // public function allUsers()
    // {
    //     $paged = 2;
    //     $users = User::orderBy('id', 'ASC')->paginate($paged);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => count($users) . ' User records fetched and paginated.',
    //         'data' => $users,
    //     ], 200);

    //     // return new JsonResponse($users, 200);

    // }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(User $user)
    {

        $user = auth()->user()->only([
            'id',
            'firstname',
            'lastname',
            'username',
            'organization_id',
            'msg_id',
            'profile_url',
            'email',
            'email_verified_at',
            'phone_number',
            'address',
            'profile_photo_path',
            'identification_type',
            'identification_number',
            'date_of_birth',
            'country_of_residence',
            'country_of_citizenship',
            'occupation',
            'industry',
            'is_politically_exposed',
            'income_source',
            'estimated_annual_income'
        ]);
        # Here we just get information about current user
        // return response()->json(auth()->user());

        // $profileUrl = Str::slug($user->firstname, $user->lastname);

        // Retrieve the organization data using the organization_id
        $organization = OrganizationModel::find($user['organization_id']);

        // Add the org_msg_id to the user data array
        if ($organization) {
            $user['org_msg_id'] = $organization->msg_id;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User profile data',
            'data' => [
                // 'profile_url' => route('users.profile', ['user' => $user]) . '/' . $profileUrl,
                'user' => $user
            ],
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('usermanagement::create');
    }

    /**
     * Create a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        try {

            $validationRules = [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'username' => 'required|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|confirmed',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generate email verification token
            $token = Str::random(60);
            $user->update(['email_token' => $token]);

            return response()->json([
                'status' => 'success',
                'data' => $user,
                'message' => 'Account created successfully. Use the Email token for email verification.',
            ], 201);
        } catch (\Exception $e) {
            // Return a JSON response with an error message
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the user: ' . $e->getMessage()
            ], 500);
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
    public function updateProfile(Request $request)
    {

        $user = auth()->user();

        $validatedData = []; // Define the variable here

        try {
            // Validate the incoming request
            $validatedData = $request->validate(
                [
                    'firstname' => 'string|nullable|max:255',
                    'lastname' => 'string|nullable|max:255',
                    'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                    'organization_id' => 'string|nullable|max:255',
                    'phone_number' => 'string|nullable|max:255|phone:NG,US,UK,GB,AU',
                    'address' => 'string|nullable|max:255',
                    'date_of_birth' => 'string|nullable|max:255',
                    'country_of_residence' => 'string|nullable|max:255',
                    'country_of_citizenship' => 'string|nullable|max:255',
                    'occupation' => 'string|nullable|max:255',
                    'industry' => 'string|nullable|max:255',
                    'interests' => 'string|nullable',
                    'is_politically_exposed' => 'string|nullable|max:255',
                    'income_source' => 'string|nullable|max:255',
                    'estimated_annual_income' => 'string|nullable|max:255',
                    // 'lastname' => 'string|nullable|max:255',
                    // Add validation rules for other fields...
                ],
                [
                    'phone_number.phone' => 'Only Nigerian, Canadian, US, Australian and UK Phone Numbers are accepted'
                ]
            );
        } catch (\Exception $e) {
            // Log error to the console and to the log file
            error_log('Failed to upload profile photo: ' . $e->getMessage());
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                error_log('Validation errors: ' . json_encode($e->errors()));
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Error uploading profile photo. File limit is 2mb',
                'errors' => ['profile_photo_path' => [$e->getMessage()]],
            ], 500);
        }

        // return $request->all();

        $sluggedUserName = Str::slug($user->username);

        if ($request->hasFile('profile_photo_path')) {
            // Ensure the directory exists
            $targetDir = public_path('assets/users/images/profile');

            // Check if the directory exists, if not create it
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            // Store the file with a new name
            // Save the file to the public directory
            $fileName = $sluggedUserName . '_avatar.' . $request->file('profile_photo_path')->getClientOriginalExtension();
            if (File::exists($targetDir . '/' . $fileName)) {
                File::delete($targetDir . '/' . $fileName);
                // return $targetDir . '/' . $fileName;
            }
            $request->file('profile_photo_path')->move($targetDir, $fileName);

            // Get the URL of the uploaded file;
            $profilePhotoUrl = '';

            // The need of this env check is to make sure that the file is uploaded to the right directory which could be different in local and production
            if (env('APP_ENV') === 'production' || env('APP_ENV') === 'development') {
                $profilePhotoUrl = url('public/assets/users/images/profile/' . $fileName);
            } else {
                $profilePhotoUrl = url('assets/users/images/profile/' . $fileName);
            }

            // If you want the absolute URL
            $user->profile_photo_path = url($profilePhotoUrl);

            $validatedData['profile_photo_path'] = url($profilePhotoUrl);
        }

        // Update the user profile
        $user->update($validatedData);

        // Return a JSON response
        return response()->json([
            'status' => 'success',
            'message' => 'User profile updated successfully.',
            'data' => $user,
        ], 200);
    }

    public function updatePassword(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'old_password' => '<PASSWORD>',
            'new_password' => '<PASSWORD>',
        ]);

        if (!Hash::check($validatedData['old_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is incorrect.'
            ], 401);
        }

        $user->password = Hash::make($validatedData['new_password']);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Verify New Email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function verifyEmail($user_id, Request $request)
    // {
    //     if (!$request->hasValidSignature()) {
    //         return response()->json(["msg" => "Invalid/Expired URL provided"], 401);
    //     }

    //     $user = User::findOrFail($user_id);

    //     if (!$user->hasVerifiedEmail()) {
    //         $user->markEmailAsVerified();
    //     }

    //     return redirect()->to('/');
    // }

    // public function resendEmail()
    // {
    //     if (auth()->user()->hasVerifiedEmail()) {
    //         return response()->json(["msg" => "Email already verified."], 400);
    //     }

    //     auth()->user()->sendEmailVerificationNotification();

    //     return response()->json(["msg" => "Email verification link sent on your email id"]);
    // }

    public function searchUsers(Request $request)
    {



        $searchTerms = [
            'query' => $request->input('query'),
            'fields' => $request->input('fields', ''),
        ];

        // return $request->input('fields', '');

        // $searchableModels = [
        //     User::class => ['firstname', 'lastname', 'username', 'profile_url', 'email', 'phone_number', 'address', 'profile_photo_path',
        //         'identification_type', 'identification_number', 'date_of_birth', 'country_of_residence', 'country_of_citizenship',
        //         'occupation', 'industry', 'is_politically_exposed', 'income_source', 'estimated_annual_income'],
        //     // Post::class => ['title', 'content'],
        // ];
        // return $searchTerms;

        $searchableModels = [
            User::class => [$searchTerms['fields']],
            // Post::class => [$searchTerms['field']],
        ];

        if (is_array($searchTerms['fields'])) {
            $searchTerms['fields'] = implode(',', $searchTerms['fields']);
        }

        // return $searchTerms['fields'];

        $searchService = new Search($searchableModels, $searchTerms);
        $results = $searchService->search();

        // return response()->json($results);

        // return $results->count();

        return response()->json([
            'status' => 'success',
            'message' => count($results) . ' query matches found.',
            'data' => $results,
        ], 200);

        // $results = User::whereEncrypted('firstname', $request->search)
        //     ->orWhereEncrypted('lastname', $request->search)
        //     ->orWhereEncrypted('username', $request->search)
        //     ->orWhereEncrypted('profile_url', $request->search)
        //     ->orWhereEncrypted('email', $request->search)
        //     ->orWhere('phone_number', $request->search)
        //     ->orWhere('date_of_birth', $request->search)
        //     ->orWhere('identification_type', $request->search)
        //     ->orWhere('identification_number', $request->search)
        //     ->orWhere('country_of_citizenship', $request->search)
        //     ->orWhere('industry', $request->search)
        //     ->orWhere('income_source', $request->search)
        //     ->orWhere('identification_type', $request->search)
        //     ->orWhere('phone_number', $request->search)
        //     ->orWhere('country_of_residence', $request->search)
        //     ->orWhere('occupation', $request->search)
        //     ->get();

        // return response()->json($results);

        // return $request->search;
        // return $searchResults = (new Search())
        //     ->registerModel(User::class, ['lastname', 'firstname'])
        //     ->search($request->search);
    }


    public function timeline(string $userSlug)
    {
        $user = User::where('username', $userSlug)->firstOrFail();

        // $user = User::find($userSlug);
        $timelineUrl = route('user.timeline', $user->username);

        // Fetch the user's timeline data or any other logic
        // return response()->json($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Timeline URL generated.',
            'data' => [
                'timelineUrl' => $timelineUrl,
                'user' => $user,
            ]
        ], 200);
    }
    public function switchTenant()
    {
        $user = auth()->user();
        $newTenantId = $user->organization_id;

        // Find the new organization
        $organization = OrganizationModel::find($newTenantId);
        if (!$organization) {
            return response()->json([
                'status' => 'error',
                'message' => 'The specified organization does not exist.'
            ], 404);
        }

        // Check if the user belongs to the new organization (if needed, based on your application logic)
        if (!$user->organizations()->where('id', $newTenantId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not belong to the specified organization.'
            ], 403);
        }

        $user->organizations()->associate($organization);
        $user->save();

        $currentOrganization = $user->organizations;

        return response()->json([
            'message' => "User '{$user->username}' switched to tenant with ID {$newTenantId}",
            'data' => [
                'user' => $user,
                'tenant' => OrganizationModel::find($newTenantId),
            ],
        ], 200);
    }

    public function userIntersts()
    {
        $interests = [
            '3D printing',
            'Amateur radio',
            'Scrapbook',
            'Amateur radio',
            'Acting',
            'Baton twirling',
            'Board games',
            'Book restoration',
            'Cabaret',
            'Calligraphy',
            'Candle making',
            'Computer programming',
            'Coffee roasting',
            'Cooking',
            'Colouring',
            'Cosplaying',
            'Couponing',
            'Creative writing',
            'Crocheting',
            'Cryptography',
            'Dance',
            'Digital arts',
            'Drama',
            'Drawing',
            'Do it yourself',
            'Electronics',
            'Embroidery',
            'Fashion',
            'Flower arranging',
            'Foreign language learning',
            'Gaming',
            'Tabletop games',
            'Role-playing games',
            'Gambling',
            'Genealogy',
            'Glassblowing',
            'Gunsmithing',
            'Homebrewing',
            'Ice skating',
            'Jewelry making',
            'Jigsaw puzzles',
            'Juggling',
            'Knapping',
            'Knitting',
            'Kabaddi',
            'Knife making',
            'Lacemaking',
            'Lapidary',
            'Leather crafting',
            'Lego building',
            'Lockpicking',
            'Machining',
            'Macrame',
            'Metalworking',
            'Magic',
            'Model building',
            'Listening to music',
            'Origami',
            'Painting',
            'Playing musical instruments',
            'Pet',
            'Poi',
            'Pottery',
            'Puzzles',
            'Quilting',
            'Reading',
            'Scrapbooking',
            'Sculpting',
            'Sewing',
            'Singing',
            'Sketching',
            'Soapmaking',
            'Sports',
            'Stand-up comedy',
            'Sudoku',
            'Table tennis',
            'Taxidermy',
            'Video gaming',
            'Watching movies',
            'Web surfing',
            'Whittling',
            'Wood carving',
            'Woodworking',
            'World Building',
            'Writing',
            'Yoga',
            'Yo-yoing',
            'Air sports',
            'Archery',
            'Astronomy',
            'Backpacking',
            'Base jumping',
            'Baseball',
            'Basketball',
            'Beekeeping',
            'Bird watching',
            'Blacksmithing',
            'Board sports',
            'Bodybuilding',
            'Brazilian jiu-jitsu',
            'Community',
            'Cycling',
            'Dowsing',
            'Driving',
            'Fishing',
            'Flag football',
            'Flying',
            'Flying disc',
            'Foraging',
            'Gardening',
            'Geocaching',
            'Ghost hunting',
            'Graffiti',
            'Handball',
            'Hiking',
            'Hooping',
            'Horseback riding',
            'Hunting',
            'Inline skating',
            'Jogging',
            'Kayaking',
            'Kite flying',
            'Kitesurfing',
            'Larping',
            'Letterboxing',
            'Metal detecting',
            'Motor sports',
            'Mountain biking',
            'Mountaineering',
            'Mushroom hunting',
            'Mycology',
            'Netball',
            'Nordic skating',
            'Orienteering',
            'Paintball',
            'Parkour',
            'Photography',
            'Polo',
            'Rafting',
            'Rappelling',
            'Rock climbing',
            'Roller skating',
            'Rugby',
            'Running',
            'Sailing',
            'Sand art',
            'Scouting',
            'Scuba diving',
            'Sculling',
            'Rowing',
            'Shooting',
            'Shopping',
            'Skateboarding',
            'Skiing',
            'Skim Boarding',
            'Skydiving',
            'Slacklining',
            'Snowboarding',
            'Stone skipping',
            'Surfing',
            'Swimming',
            'Taekwondo',
            'Tai chi',
            'Urban exploration',
            'Vacation',
            'Vehicle restoration',
            'Water sports'
        ];

        return response()->json([
            'message' => "Success",
            'data' => $interests,
        ], 200);
    }

    /**
     * Organizations user belongs to .
     */
    public function connectedOrganizations()
    {
        $user = auth()->user();
        $connectedOrganizations = $user->organizations()->get(['id', 'org_name']);

        return response()->json($connectedOrganizations);
    }

    public function switchBusiness($organizationId)
    {
        try {
            $user = auth()->user();
            // $token = request()->bearerToken(); // Extract the token

            $organization = OrganizationModel::findOrFail($organizationId, ['id', 'org_name', 'msg_id', 'email']);

            // Check if the user is connected to this organization
            if (!$user->organizations()->where('organizations.id', $organizationId)->exists()) {
                return response()->json(['error' => 'User does not belong to the specified organization'], 403);
            }

            $user->organization_id = $organizationId;

            if ($user->save()) {
                // log user org switch activity
                $this->logUserActivity($user->id, $organizationId, 'switched', request()->ip());
                // $newToken = auth()->refresh();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Switched to business successfully',
                    'data' => [
                        'org' => $organization,
                        'user' => User::where('id', auth()->user()->id)->get(['id', 'username', 'msg_id', 'email']),
                        // 'authToken' => $newToken
                    ]
                ], 200);

            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed. Unable to switch into business.',
                ], 403);
            }

            // Log user activity
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    public function logoutBusiness()
    {
        try {
            $user = auth()->user();
            // $token = request()->bearerToken(); // Extract the token

            $organization = OrganizationModel::findOrFail($user->organization_id, ['id', 'org_name', 'msg_id', 'email']);

            // Check if the user is connected to this organization
            if (!$user->organizations()->where('organizations.id', $organization->id)->exists()) {
                return response()->json(['error' => 'User does not belong to the specified organization'], 403);
            }

            $user = auth()->user();
            $organizationId = $user->organization_id;

            $user->organization_id = null;

            if ($user->save()) {
                $this->logUserActivity($user->id, $organizationId, 'logged out', request()->ip());

                // $newToken = auth()->refresh();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully logged out of every business',
                    'data' => [
                        'org' => $organization,
                        'user' => User::where('id', auth()->user()->id)->get(['id', 'username', 'msg_id', 'email']),
                        // 'authToken' => $newToken
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed. Unable to logout of business accounts.',
                ], 403);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    private function logUserActivity($userId, $organizationId, $action, $ipAddress)
    {
        UserActivityHistoryModel::create([
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'action' => $action,
            'ip_address' => $ipAddress,
        ]);
    }
    public function getAllUsers()
    {
        $user = auth()->user();

        // if ($user->status !== 'user-admin') {
        //     return response()->json(['error' => 'Forbidden'], 403);
        // }

        $users = User::all();
        return response()->json(['status' => 'success', 'data' => $users], 200);
    }
}
