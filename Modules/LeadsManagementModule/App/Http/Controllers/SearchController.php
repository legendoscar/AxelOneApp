<?php

namespace Modules\LeadsManagementModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;

use App\Models\User;
use Modules\LeadsManagementModule\App\Services\SynonymService;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
// use Modules\LeadsManagementModule\App\Services\SearchBusiness;
use Modules\LeadsManagementModule\App\Services\SearchBusiness;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Modules\LeadsManagementModule\App\Models\SearchLeadsModel;
use Modules\LeadsManagementModule\App\Notifications\BusinessMatchedFromSearchNotification;
use Modules\LeadsManagementModule\App\Notifications\NewLeadFromSearchNotification;
use Modules\LeadsManagementModule\App\Notifications\NewLeadCreatedNotification;
use Modules\LeadsManagementModule\App\Events\NewLeadCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\SendPulseService;
use Tymon\JWTAuth\Facades\JWTAuth;



class SearchController extends Controller
{

    // public function searchBusiness(Request $request, SendPulseService $sendPulseService)
    // {

    //     $searchTerms = [
    //         'query' => $request->input('query'),
    //         'fields' => $request->input('fields', ''),
    //     ];

    //     $startTime = microtime(true);
    //     $duration = microtime(true) - $startTime;

    //     // return $request->input('fields', '');

    //     // $searchableModels = [
    //     //     User::class => ['firstname', 'lastname', 'username', 'profile_url', 'email', 'phone_number', 'address', 'profile_photo_path',
    //     //         'identification_type', 'identification_number', 'date_of_birth', 'country_of_residence', 'country_of_citizenship',
    //     //         'occupation', 'industry', 'is_politically_exposed', 'income_source', 'estimated_annual_income'],
    //     //     // Post::class => ['title', 'content'],
    //     // ];
    //     // return $searchTerms;

    //     $searchableModels = [
    //         OrganizationModel::class => [$searchTerms['fields']],
    //         // Post::class => [$searchTerms['field']],
    //     ];

    //     if (is_array($searchTerms['fields'])) {
    //         $searchTerms['fields'] = implode(',', $searchTerms['fields']);
    //     }

    //     // return $searchTerms['fields'];

    //     $searchService = new SearchBusiness($searchableModels, $searchTerms, $sendPulseService);
    //     $results = $searchService->search();


    //     // Extract org_id from search results
    //     $orgIds = $results->pluck('id')->toArray();

    //     // Convert array to JSON object
    //     // $orgIdsJson = json_encode($orgIds);

    //     // Log the search request
    //     $searchRequestModel = SearchRequestModel::create([
    //         'user_id' => auth()->user()->id, // If the user is authenticated
    //         'search_term' => $searchTerms['query'],
    //         'ip_address' => $request->ip(),
    //         'user_agent' => $request->userAgent(),
    //         'search_filters' => $searchTerms['fields'],
    //         'results_count' => count($results),
    //         'org_matched' => $orgIds, // Assuming 'org_ids' is the column where you want to store the JSON object
    //         'duration' => $duration
    //     ]);

    //     $searchRequestModel->setStatus('active', 'new search request');



    //     // Create search request with JSON object
    //     // Create the lead
    //     if (count($results) >= 1) {
    //         $leadCreate = SearchLeadsModel::create([
    //             'search_request_id' => $searchRequestModel->id,
    //             'user_id' => $searchRequestModel->user_id,
    //             'data' => $request->input('data', []),
    //             'status' => $request->input('status', 'new'), // Default status is 'new'
    //         ]);

    //         $leadCreate->setStatus('new', 'just a new lead open for bidding');


    //         // $user = auth()->user();
    //         $payload = [
    //             'searchTerm' => $searchTerms['query'],
    //             'searchFields' => [$searchTerms['fields']]
    //         ];

    //         // $lead = User::join('search_requests', 'search_requests.user_id', '=', 'users.id')
    //         // ->join('search_leads', 'search_leads.search_request_id', '=', 'search_requests.id')
    //         // ->where('search_leads.id', $leadCreate->id)
    //         // ->first(['search_leads.id as id', 'search_term']);

    //         // Send notification to each matched business
    //         foreach ($results as $business) {
    //             // Notification::send($business, new BusinessMatchedFromSearchNotification($user, $this->searchTerms['query'], $searchFields, $business));
    //             // Log the response for debugging purposes
    //             // Log::info('Webhook sent', [
    //             //     'data' => $payload,
    //             //     'business' => [
    //             //         'id' => $business->id,
    //             //         'name' => $business->org_name
    //             //     ]
    //             //     // 'response' => response()->body(),
    //             // ]);
    //             $business->notify(new NewLeadFromSearchNotification($leadCreate, $payload));

    //             // broadcast(new NewLeadCreated($user, $payload))->toOthers();
    //         }


    //     }

    //     // return response()->json($results);

    //     // return $results->count();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => count($results) . ' query matches found.',
    //         'data' => $results,
    //     ], 200);

    //     // $results = User::whereEncrypted('firstname', $request->search)
    //     //     ->orWhereEncrypted('lastname', $request->search)
    //     //     ->orWhereEncrypted('username', $request->search)
    //     //     ->orWhereEncrypted('profile_url', $request->search)
    //     //     ->orWhereEncrypted('email', $request->search)
    //     //     ->orWhere('phone_number', $request->search)
    //     //     ->orWhere('date_of_birth', $request->search)
    //     //     ->orWhere('identification_type', $request->search)
    //     //     ->orWhere('identification_number', $request->search)
    //     //     ->orWhere('country_of_citizenship', $request->search)
    //     //     ->orWhere('industry', $request->search)
    //     //     ->orWhere('income_source', $request->search)
    //     //     ->orWhere('identification_type', $request->search)
    //     //     ->orWhere('phone_number', $request->search)
    //     //     ->orWhere('country_of_residence', $request->search)
    //     //     ->orWhere('occupation', $request->search)
    //     //     ->get();

    //     // return response()->json($results);

    //     // return $request->search;
    //     // return $searchResults = (new Search())
    //     //     ->registerModel(User::class, ['lastname', 'firstname'])
    //     //     ->search($request->search);
    // }

    // public function createLeadFromSearch(Request $request)
    // {
    //     $searchRequestId = $request->input('search_request_id');
    //     $searchRequest = SearchRequestModel::findOrFail($searchRequestId);

    //     // Create the lead
    //     $lead = SearchLeadsModel::create([
    //         'search_request_id' => $searchRequest->id,
    //         'user_id' => $searchRequest->user_id,
    //         'data' => $request->input('data', []),
    //     ]);

    //     return response()->json($lead);
    // }

    /* Search for business */
    public function search(Request $request, SynonymService $synonymService, SendPulseService $sendPulseService)
    {

        $request->validate([
            'searchTerms' => 'required|array',
            'searchTerms.0' => 'required|string', // service name
            'searchTerms.1' => 'required|string', // location
            'searchTerms.2' => 'nullable|integer', // category ID
        ]);

        $searchTerms = $request->input('searchTerms');
        $models = [
            OrganizationModel::class => ['org_name', 'org_bio', 'address', 'city', 'state', 'country', 'zipcode', 'lat', 'long']
        ];

        $searchService = new SearchBusiness($models, $searchTerms, $synonymService, $sendPulseService);
        $results = $searchService->search();

        return response()->json([
            'status' => 'success',
            // 'message' => count($results) . ' businesses found',
            'message' => 'Search successful',
            'data' => $results
        ]);
    }


}
