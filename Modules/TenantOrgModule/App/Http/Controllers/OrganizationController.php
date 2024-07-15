<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SendPulseService;
use File;
use Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Log;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class OrganizationController extends Controller
{

    protected $sendPulseService;

    public function __construct(SendPulseService $sendPulseService)
    {
        $this->sendPulseService = $sendPulseService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orgs = OrganizationModel::with(['businessCategories:id,name', 'businessSubUnits:id,name', 'locations'])
            ->orderBy('id', 'DESC')->paginate(100, [
                    'id',
                    'org_name',
                    'msg_id',
                    'org_bio',
                    'logo',
                    'cover_image',
                    'subdomain',
                    'phone',
                    'email',
                    'website',
                    'industry',
                    'size',
                    'location',
                    'products',
                    'services',
                    'business_hours',
                    'website_social_media',
                    'contact_info',
                    'reviews_ratings',
                    'pricing',
                    'certifications_accreditations',
                    'languages_spoken',
                    'payment_methods',
                    'nearby_landmarks',
                    'parking_info',
                    'pet_policy',
                    'dress_code',
                    'special_instructions',
                    'accessibility',
                    'events_promotions',
                    'cancellation_policy',
                    'environmental_practices',
                    'awards_nominations',
                    'user_generated_contents',
                ]);
        return new JsonResponse($orgs, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createTenantOrg(Request $request)
    {

        // return $request->all();
        // Validate the request data
        $validatedData = $request->validate(
            [
                'org_name' => 'required|string|max:200',
                'org_bio' => 'string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // 'subdomain' => 'string',
                'business_category_ids' => 'required|array',
                'business_category_ids.*' => 'exists:business_categories,id',
                'business_unit_ids' => 'required|array',
                'business_unit_ids.*' => 'exists:business_sub_units,id',
                'address' => 'string|nullable',
                'city' => 'string|nullable',
                'state' => 'string|nullable',
                'zipcode' => 'string|nullable',
                'country' => 'string|nullable',
                'lat' => 'string|nullable',
                'long' => 'string|nullable',
                'phone' => 'string|nullable|max:255|phone:NG,US,UK,GB,AU',
                'email' => 'string|email|max:255|unique:organizations',
                'website' => 'nullable|max:255|regex:/^(https?:\/\/)?(www\.)?([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,}(:[0-9]+)?(\/.*)?$/',
                'size' => 'string|nullable',
                // Add more validation rules as needed
            ],
            [
                'phone.phone' => 'Only Nigerian, Canadian, US, Australian and UK Phone Numbers are accepted'
            ]
        );

        DB::beginTransaction();
        try {

            // Retrieve the authenticated user (creator)
            $user = User::find(Auth::user()->id);

            // set the database name
            $databaseName = env('TENANT_DB_PREFIX') . $validatedData['org_name'];

            // Associate the creator user with the new tenant
            $orgModel = new OrganizationModel;
            // $orgModel->creator_id = $user->id;
            // $orgModel->org_name = $validatedData['org_name'];
            $orgModel->logo = !empty($validatedData['logo']) ? $validatedData['logo'] : '';
            $orgModel->cover_image = !empty($validatedData['cover_image']) ? $validatedData['cover_image'] : '';
            // $orgModel->org_bio = $validatedData['org_bio'];

            $orgModel->website = !empty($validatedData['website']) ? $validatedData['website'] : '';
            $orgModel->size = !empty($validatedData['size']) ? $validatedData['size'] : '';
            // Create the new organization with their data

            $sluggedOrgName = Str::slug($validatedData['org_name']);

            if ($request->hasFile('logo')) {
                // Ensure the directory exists
                if (!Storage::exists('assets/business/images/logos')) {
                    Storage::makeDirectory('assets/business/images/logos');
                }
                // Store the file with a new name
                $logoPath = $request->file('logo')->storeAs(
                    'assets/business/images/logos',
                    $sluggedOrgName . '_logo.' . $request->file('logo')->getClientOriginalExtension()
                );
                $orgModel->logo = str_replace('', '', $logoPath); // Remove '' from the path
            }

            if ($request->hasFile('cover_image')) {
                // Ensure the directory exists
                $targetDir = public_path('assets/business/images/cover_image');

                // Check if the directory exists, if not create it
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                // Store the file with a new name
                // Save the file to the public directory
                $coverImageName = $sluggedOrgName . '_cover_image.' . $request->file('cover_image')->getClientOriginalExtension();
                if (File::exists($targetDir . '/' . $coverImageName)) {
                    File::delete($targetDir . '/' . $coverImageName);
                    // return $targetDir . '/' . $coverImageName;
                }
                $request->file('cover_image')->move($targetDir, $coverImageName);
            }

            // return $validatedData;

            // Extract only the desired fields
            $fieldsToCreate = [
                'org_name',
                'org_bio',
                'phone',
                'email',
                'website',
                'size'
            ];
            $filteredData = array_filter($validatedData, function ($key) use ($fieldsToCreate) {
                return in_array($key, $fieldsToCreate);
            }, ARRAY_FILTER_USE_KEY);

            $filteredData['creator_id'] = $user->id;
            $filteredData['database_name'] = $databaseName;
            $filteredData['subdomain'] = $sluggedOrgName;

            $organization = OrganizationModel::create($filteredData);

            $organization->locations()->create([
                'organization_id' => $organization->id,
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zipcode' => $validatedData['zipcode'],
                'country' => $validatedData['country'],
                'location_name' => 'Head Office', // Include any other necessary fields
                'lat' => !empty($validatedData['lat']) ? $validatedData['lat'] : '',
                'long' => !empty($validatedData['long']) ? $validatedData['long'] : '',
            ]);

            // Automatically create a wallet for the organization
            $organization->createWallet([
                'name' => $validatedData['org_name'],
                'slug' => $validatedData['org_name'],
            ]);


            $organization->businessCategories()->attach($request->business_category_ids);
            $organization->businessSubUnits()->attach($request->business_unit_ids);

            DB::table('organization_user')->insert([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
            ]);

            // return ['user' => $user, 'org' => $orgModel];


            // Create the new tenant

            // return $orgModel = OrganizationModel::with(['businessCategories:id,name', 'businessSubUnits:id,name'])->get($orgModel->id);
            // $tenant = Tenant::create([
            //     'id' => $validatedData['org_name'],
            //     'domain' => $validatedData['subdomain'],
            //     'name' => $validatedData['org_name'],
            //     'tenancy_db_name' => $databaseName
            //     // Add more tenant attributes as needed
            // ]);

            // return ['user' => $user, 'org' => $orgModel, 'tenant' => $tenant];

            // Create the tenant's database
            // \DB::unprepared("CREATE DATABASE $databaseName");

            // // Retrieve the authenticated user (creator)
            // $user = User::find(Auth::user()->id);

            // // Associate the creator user with the new tenant
            // $user->organization_id = $tenant->id;
            // $user->save();

            // // Optionally, you may want to create a new user for the creator in the tenant's user table
            // $tenantUser = $tenant->users()->create([
            //     'name' => $user->name,
            //     'email' => $user->email,
            //     // Add more user attributes as needed
            // ]);

            // // Run tenant-specific migrations
            // $path = 'tenant';
            // $this->call('migrate', [
            //     '--path' => "app/database/migrations/$path",
            //     '--database' => $databaseName,
            // ]);

            DB::commit();
            $htmlContent = view('emails.business.new-business', ['businessname' => $organization->org_name])->render();

            $this->sendPulseService->sendEmail($user->email, 'Welcome to Fyndah - where your business will find amplified success!', $htmlContent);

            // Log before calling the webhook function
            Log::info('Calling sendNewOrgOnboardingMessageWebhook for organization:', ['organization' => $organization]);
            $this->sendNewOrgOnboardingMessageWebhook($organization);

            return response()->json([
                'message' => 'Organization created successfully',
                'data' => [
                    // 'tenant' => $tenant,
                    'organization' => $organization,
                    // 'creator_user' => $user,
                    // 'wallet' => $orgModel->wallet
                    // Add more data as needed
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error placing bid: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create business', 'message' => $e->getMessage()], 500);
        }
    }

    public function sendNewOrgOnboardingMessageWebhook($org)
    {
        //the webhook payload
        $payload = [
            'org_msg_id' => $org->msg_id,
            'org_name' => $org->org_name,
            'org_id' => $org->id,
        ];

        try {
            $webhookUrl = 'https://axelonepostfeature.onrender.com/api/messages/webhook/org-registered';
            $response = Http::withHeaders([
                'Content-type' => 'application/json'
            ])->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Webhook sent successfully', [
                    'org' => [
                        'org_msg_id' => $org->msg_id,
                        'org_name' => $org->org_name,
                        'org_id' => $org->id,
                    ]
                ]);
            } else {
                Log::error('Failed to send webhook notification to org', [
                    'org' => [
                        'org_msg_id' => $org->msg_id,
                        'org_name' => $org->org_name,
                        'org_id' => $org->id,
                    ],
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending webhook notification to org', [
                'org' => [
                    'org_msg_id' => $org->msg_id,
                    'org_name' => $org->org_name,
                    'org_id' => $org->id,
                ],
                'exception_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fetch Org Profile.
     */
    public function fetchOrgProfile($org_key)
    {

        $org_key = request()->org_key;

        // First, try to find an organization by ID
        $org = OrganizationModel::find($org_key);

        // If no organization is found, try to find by name
        if (!$org) {
            $org = OrganizationModel::where('org_name', $org_key)
                ->with(['locations'])
                // ->orWhere('subdomain', $org_key)
                ->first([
                    'id',
                    'org_name',
                    'logo',
                    'cover_image',
                    'org_bio',
                    'subdomain',
                    'address',
                    'city',
                    'state',
                    'zipcode',
                    'country',
                    'phone',
                    'email',
                    'website',
                    'industry',
                    'size',
                    'location',
                    'products',
                    'services',
                    'business_hours',
                    'website_social_media',
                    'contact_info',
                    'reviews_ratings',
                    'pricing',
                    'certifications_accreditations',
                    'languages_spoken',
                    'payment_methods',
                    'nearby_landmarks',
                    'parking_info',
                    'pet_policy',
                    'dress_code',
                    'special_instructions',
                    'accessibility',
                    'events_promotions',
                    'cancellation_policy',
                    'environmental_practices',
                    'awards_nominations',
                    'user_generated_contents',

                ]);
        }
        // $org = OrganizationModel::where('id', $org_key)
        //     ->orWhere('org_name', $org_key)
        //     ->orWhere('subdomain', $org_key)
        //     ->first();

        if (!$org) {
            return response()->json([
                'status' => 'error',
                'exists' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'exists' => true,
            'message' => 'Organization found',
            'data' => $org
        ], 200);
    }

    /**
     * Fetch Org Locations.
     */
    public function fetchOrgLocations($org_key)
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

            $location = $org->location;

            return response()->json([
                'status' => 'success',
                'message' => is_null($location) ? '0 locations found' : count($location) . ' locations found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'location' => $location
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

    /**
     * Fetch Org Products
     */
    public function fetchOrgProducts($org_key)
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

            $products = $org->products;

            return response()->json([
                'status' => 'success',
                'message' => is_null($products) ? '0 products found' : count($products) . ' products found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'products' => $products
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
    public function updateOrganization(Request $request)
    {
        $user = auth()->user();

        if (is_null($user->organization_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not belong to any organization.',
            ], 403);
        }

        $validatedData = []; // Define the variable here

        DB::beginTransaction();

        try {
            // Validate the request
            $validatedData = $request->validate([
                'org_name' => 'nullable|string|max:255',
                'org_bio' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zipcode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'industry' => 'nullable|string|max:255',
                'size' => 'nullable|integer',
            ]);

            $org_id = $user->organization_id;
            $orgModel = OrganizationModel::findOrFail($org_id);

            // Update the organization fields
            $orgData = $request->only([
                'org_name',
                'org_bio',
                'phone',
                'email',
                'website',
                'size',
            ]);

            $orgName = isset($validatedData['org_name']) ? $validatedData['org_name'] : $orgModel->org_name;
            $sluggedOrgName = Str::slug($orgName);

            if ($request->hasFile('logo')) {
                // Ensure the directory exists
                $targetDir = public_path('assets/business/images/logos');

                // Check if the directory exists, if not create it
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                // Store the file with a new name
                // Save the file to the public directory
                $fileName = $sluggedOrgName . '_logo.' . $request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->move($targetDir, $fileName);

                // Get the URL of the uploaded file
                $logoUrl = '';

                // The need of this env check is to make sure that the file is uploaded to the right directory which could be different in local and production
                if (env('APP_ENV') === 'production' || env('APP_ENV') === 'development') {
                    $logoUrl = url('public/assets/business/images/logos/' . $fileName);
                } else {
                    $logoUrl = url('assets/business/images/logos/' . $fileName);
                }

                // If you want the absolute URL
                $orgModel->logo = url($logoUrl);
            }

            if ($request->hasFile('cover_image')) {
                // Ensure the directory exists
                $targetDir = public_path('assets/business/images/cover_image');

                // Check if the directory exists, if not create it
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                // Store the file with a new name
                // Save the file to the public directory
                $fileName = $sluggedOrgName . '_cover_image.' . $request->file('cover_image')->getClientOriginalExtension();
                $request->file('cover_image')->move($targetDir, $fileName);

                // Get the URL of the uploaded file
                $coverImageUrl = '';

                // The need of this env check is to make sure that the file is uploaded to the right directory which could be different in local and production
                if (env('APP_ENV') === 'production' || env('APP_ENV') === 'development') {
                    $coverImageUrl = url('public/assets/business/images/cover_image/' . $fileName);
                } else {
                    $coverImageUrl = url('assets/business/images/cover_image/' . $fileName);
                }

                // If you want the absolute URL
                $orgModel->cover_image = url($coverImageUrl);
            }

            $orgModel->update($orgData);

            // Update the locations relationship
            $locationData = $request->only(['address', 'city', 'state', 'zipcode', 'country']);
            $orgModel->locations()->updateOrCreate([], $locationData);

            $updatedOrg = OrganizationModel::with('locations')->findOrFail($org_id);

            $responseData = [
                'id' => $updatedOrg->id,
                'org_name' => $updatedOrg->org_name,
                'org_bio' => $updatedOrg->org_bio,
                'logo' => $updatedOrg->logo,
                'cover_image' => $updatedOrg->cover_image,
                'msg_id' => $updatedOrg->msg_id,
                'phone' => $updatedOrg->phone,
                'email' => $updatedOrg->email,
                'website' => $updatedOrg->website,
                'size' => $updatedOrg->size,
                'locations' => $updatedOrg->locations->map(function ($location) {
                    return [
                        'id' => $location->id,
                        'organization_id' => $location->organization_id,
                        'location_name' => $location->location_name,
                        'country' => $location->country,
                        'state' => $location->state,
                        'city' => $location->city,
                        'address' => $location->address,
                        'zipcode' => $location->zipcode,
                        'lat' => $location->lat,
                    ];
                })
            ];

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Organization updated successfully.',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the organization.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Check if user belongs to org.
     */
    public function checkUserBelongsToOrganization(Request $request)
    {
        $userId = $request->user_id;
        $organizationId = $request->org_id;

        $user = User::find($userId);
        $organization = OrganizationModel::find($organizationId);

        if ($user && $user->organizations->contains($organization)) {
            return response()->json(['message' => 'User belongs to the organization.'], 200);
        } else {
            return response()->json(['message' => 'User does not belong to the organization.'], 400);
        }
    }


    /**
     * Users belonging to and organization.
     */
    public function connectedUsers(Request $request)
    {
        $organizationId = $request->org_id;
        $organization = OrganizationModel::find($organizationId);
        $connectedUsers = $organization->users()->get(['id', 'username']);

        return response()->json($connectedUsers);
    }

    public function attachCategoriesAndSubCategories(Request $request, OrganizationModel $organization)
    {
        $request->validate([
            'org_cat_id' => 'array',
            'org_cat_id.*' => 'exists:business_categories,id',
            'org_sub_cat_id' => 'required',
            'org_sub_cat_id.*' => 'exists:business_sub_units,id',
        ]);

        // Get the current number of categories attached to the organization
        $currentCategoryCount = $organization->businessCategories()->count();

        // Check if the new categories will exceed the maximum limit of 3
        if (($currentCategoryCount + count($request->org_sub_cat_id)) > 3) {
            return response()->json([
                'message' => 'An organization can have a maximum of 3 categories.'
            ], 422);
        }

        // Attach business categories
        $organization = OrganizationModel::find($request->id);
        $organization->businessCategories()->syncWithoutDetaching($request->org_cat_id);

        // Attach business sub-units
        $organization->businessSubUnits()->syncWithoutDetaching($request->org_sub_cat_id);

        return response()->json(['message' => 'Business categories and sub-units assigned to organization successfully']);
    }

    public function featuredorganizations()
    {
        $featuredOrganizations = OrganizationModel::with(['businessCategories:id,name', 'businessSubUnits:id,name', 'locations'])
            ->currentStatus('featured')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $featuredOrganizations,
        ], 200);
    }
}
