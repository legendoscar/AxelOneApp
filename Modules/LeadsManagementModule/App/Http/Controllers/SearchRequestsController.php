<?php

namespace Modules\LeadsManagementModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

use App\Models\User;
use Modules\LeadsManagementModule\App\Models\BidsModel;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
// use Modules\LeadsManagementModule\App\Services\SearchBusiness;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class SearchRequestsController extends Controller
{

    /* Search Requests */
    private function searchRequests($status = null, Request $request)
    {
        try {

            // return $request->all();

            // Validation rules for dates
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            // Building the query to fetch search requests with the user's details
            $query = SearchRequestModel::with('user:id,msg_id,username,firstname,lastname,profile_url,email,phone_number,address,profile_photo_path,identification_type,identification_number,date_of_birth,country_of_residence,country_of_citizenship,occupation,industry,is_politically_exposed,income_source,estimated_annual_income,interests')
                ->whereJsonContains('org_matched', auth()->user()->organization_id)
                ->orderBy('created_at', 'DESC');

            // If a status is provided, filter by status
            if ($status) {
                $query->currentStatus($status);
            }

            // if dates are provided, filter by the dates
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Fetch the search requests
            $searchRequests = $query->get(['id', 'search_term', 'search_filters', 'user_id', 'created_at']);

            // Format the created_at date and include user details
            $searchRequests->transform(function ($request) {
                $createdAt = $request->getOriginal('created_at'); // Get the original created_at value
                $request->search_datetime = Carbon::parse($createdAt)->format('D, jS F Y, h:i:sA');
                $request->search_datetime_ago = Carbon::parse($createdAt)->diffForHumans(); // format with ago
                $request->search_by = [
                    'user_id' => $request->user_id,
                    'msg_id' => $request->user->msg_id,
                    'firstname' => $request->user->firstname ?? 'N/A',
                    'lastname' => $request->user->lastname ?? 'N/A',
                    'username' => $request->user->username ?? 'N/A',
                    'profile_url' => $request->user->profile_url ?? 'N/A',
                    'email' => $request->user->email ?? 'N/A',
                    'phone_number' => $request->user->phone_number ?? 'N/A',
                    'address' => $request->user->address ?? 'N/A',
                    'profile_photo_path' => $request->user->profile_photo_path ?? 'N/A',
                    'date_of_birth' => $request->user->date_of_birth ?? 'N/A',
                    'occupation' => $request->user->occupation ?? 'N/A',
                    'interests' => $request->user->interests ?? 'N/A'
                ];
                $request->status = $request->status()->first()->name ?? 'N/A';
                unset($request->user_id, $request->user); // Remove the user_id and user relationship from the response
                return $request;
            });

            return $searchRequests;

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching search requests: ' . $e->getMessage());

            // Return a JSON response with an error message and status code
            return response()->json([
                'status' => 'Error. Unable to fetch search requests',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /* Get active searches for the current user organization */
    public function activeSearchRequests(Request $request)
    {

        try {
            $activeSearchRequests = $this->searchRequests('active', $request);

            // Returning the active search requests as JSON
            return response()->json([
                'status' => 'success',
                'message' => count($activeSearchRequests) . ' active search requests found',
                'data' => $activeSearchRequests
            ], 200);

        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch active search requests at this time'
            ], 500);
        }
    }


    /* Get search requests history for the current user organization */
    public function getSearchRequestsHistory(Request $request)
    {

        try {

            $searchHistory = $this->searchRequests(null, $request);

            // Returning the active search requests as JSON
            return response()->json([
                'status' => 'success',
                'message' => count($searchHistory) . ' search requests found',
                'data' => $searchHistory
            ], 200);

        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch search requests at this time'
            ], 500);
        }
    }


    /* View specific search requests */
    public function fetchSearchRequestById($id)
    {
        try {
            // Find the search request by ID with user details
            $searchRequest = SearchRequestModel::with('user:id,msg_id,username,firstname,lastname,profile_url,email,phone_number,address,profile_photo_path,identification_type,identification_number,date_of_birth,country_of_residence,country_of_citizenship,occupation,industry,is_politically_exposed,income_source,estimated_annual_income,interests')
                ->findOrFail($id);

            // Format the created_at date and include user details
            $createdAt = $searchRequest->getOriginal('created_at');
            $searchRequest->search_datetime = Carbon::parse($createdAt)->format('D, jS F Y, h:i:sA');
            $searchRequest->search_datetime_ago = Carbon::parse($createdAt)->diffForHumans();
            $searchRequest->search_by = [
                'user_id' => $searchRequest->user_id,
                'msg_id' => $searchRequest->user->msg_id ?? 'N/A',
                'firstname' => $searchRequest->user->firstname ?? 'N/A',
                'lastname' => $searchRequest->user->lastname ?? 'N/A',
                'username' => $searchRequest->user->username ?? 'N/A',
                'profile_url' => $searchRequest->user->profile_url ?? 'N/A',
                'email' => $searchRequest->user->email ?? 'N/A',
                'phone_number' => $searchRequest->user->phone_number ?? 'N/A',
                'address' => $searchRequest->user->address ?? 'N/A',
                'profile_photo_path' => $searchRequest->user->profile_photo_path ?? 'N/A',
                'date_of_birth' => $searchRequest->user->date_of_birth ?? 'N/A',
                'occupation' => $searchRequest->user->occupation ?? 'N/A',
                'interests' => $searchRequest->user->interests ?? 'N/A'
            ];
            $searchRequest->status = $searchRequest->status()->first()->name ?? 'N/A';
            unset($searchRequest->user_id, $searchRequest->user); // Remove the user_id and user relationship from the response

            // Return a success response with the search request details
            return response()->json([
                'status' => 'success',
                'data' => $searchRequest
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching search request: ' . $e->getMessage());

            // Return a JSON response with an error message and status code
            return response()->json([
                'status' => 'Error. Unable to fetch search request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* Fetch search requests by current authed user */
    public function getUserSearchRequests(Request $request)
    {
        try {

            // Validation rules for dates
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
            // Get the authenticated user ID
            $userId = auth()->user()->id;

            // Fetch the user's search requests with the user's details
            $userSearchRequests = SearchRequestModel::with('user:id,msg_id,username,firstname,lastname,profile_url,email,phone_number,address,profile_photo_path,identification_type,identification_number,date_of_birth,country_of_residence,country_of_citizenship,occupation,industry,is_politically_exposed,income_source,estimated_annual_income,interests')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->get(['id', 'search_term', 'search_filters', 'created_at', 'user_id']);

            // Format the created_at date and include user details
            $userSearchRequests->transform(function ($request) {
                $createdAt = $request->getOriginal('created_at');
                $request->search_datetime = Carbon::parse($createdAt)->format('D, jS F Y, h:i:sA');
                $request->search_datetime_ago = Carbon::parse($createdAt)->diffForHumans();
                $request->search_by = [
                    'user_id' => $request->user_id,
                    'msg_id' => $request->user->msg_id ?? 'N/A',
                    'firstname' => $request->user->firstname ?? 'N/A',
                    'lastname' => $request->user->lastname ?? 'N/A',
                    'username' => $request->user->username ?? 'N/A',
                    'profile_url' => $request->user->profile_url ?? 'N/A',
                    'email' => $request->user->email ?? 'N/A',
                    'phone_number' => $request->user->phone_number ?? 'N/A',
                    'address' => $request->user->address ?? 'N/A',
                    'profile_photo_path' => $request->user->profile_photo_path ?? 'N/A',
                    'date_of_birth' => $request->user->date_of_birth ?? 'N/A',
                    'occupation' => $request->user->occupation ?? 'N/A',
                    'interests' => $request->user->interests ?? 'N/A'
                ];
                $request->status = $request->status()->first()->name ?? 'N/A';
                unset($request->user_id, $request->user);
                return $request;
            });

            // Returning the user's search requests as JSON
            return response()->json([
                'status' => 'success',
                'message' => count($userSearchRequests) . ' search requests found',
                'data' => $userSearchRequests
            ], 200);
        } catch (Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching user search requests: ' . $e->getMessage());

            // Return a JSON response with an error message and status code
            return response()->json([
                'status' => 'Error. Unable to fetch user search requests',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /* the organizations that bid on a specific search request */

    public function getOrganizationsThatBidOnTheSearchRequest(Request $request, $searchRequestId)
    {
        try {

            // Validation rules for dates
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
            // Fetch the search request
            $searchRequest = SearchRequestModel::with('user')->findOrFail($searchRequestId);

            // Fetch the bids for the search request along with the organization and status
            $query = BidsModel::with(['organization', 'statuses'])
                ->where('search_request_id', $searchRequestId)
                ->orderBy('created_at', 'DESC');

            // if dates are provided, filter by the dates
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $bids = $query->get();

            // Format the bids data
            $bids->transform(function ($bid) use ($searchRequest) {
                $createdAt = Carbon::parse($bid->created_at);
                $statusName = $bid->statuses->last()->name ?? 'N/A';

                return [
                    'bid_id' => $bid->id,
                    'bid_amount' => $bid->bid_currency . $bid->bid_amount,
                    'bid_datetime' => $createdAt->format('D, jS F Y, h:i:sA'),
                    'bid_datetime_ago' => $createdAt->diffForHumans(),
                    'status' => $statusName,
                    'organization' => [
                        'id' => $bid->organization->id ?? 'N/A',
                        'name' => $bid->organization->org_name ?? 'N/A',
                        'org_msg_id' => $bid->organization->msg_id ?? 'N/A',
                        'logo' => $bid->organization->logo ?? 'N/A',
                    ],
                    'search_request' => [
                        'search_request_id' => $searchRequest->id,
                        'search_term' => $searchRequest->search_term ?? 'N/A',
                        'search_filters' => $searchRequest->search_filters ?? 'N/A',
                        'ip_address' => $searchRequest->ip_address ?? 'N/A',
                        'user_agent' => $searchRequest->user_agent ?? 'N/A',
                        'search_datetime' => Carbon::parse($searchRequest->created_at)->format('D, jS F Y, h:i:sA'),
                        'search_datetime_ago' => Carbon::parse($searchRequest->created_at)->diffForHumans(),
                        'status' => $searchRequest->status()->first()->name ?? 'N/A',
                        'search_by' => [
                            'user_id' => $searchRequest->user_id,
                            'msg_id' => $searchRequest->user->msg_id ?? 'N/A',
                            'firstname' => $searchRequest->user->firstname ?? 'N/A',
                            'lastname' => $searchRequest->user->lastname ?? 'N/A',
                            'username' => $searchRequest->user->username ?? 'N/A',
                            'email' => $searchRequest->user->email ?? 'N/A',
                        ],
                    ],
                ];
            });

            // Return the formatted data
            return response()->json([
                'status' => 'success',
                'data' => $bids,
            ], 200);
        } catch (Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error fetching organizations by search request: ' . $e->getMessage());

            // Return a JSON response with an error message and status code
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch organizations by search request',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    public function checkAndCloseSearchRequests()
    {
        $searchRequests = SearchRequestModel::where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('bids', function ($q) {
                    $q->havingRaw('COUNT(*) >= 5');
                })
                    ->orWhere('created_at', '<=', now()->subHours(6));
            })
            ->get();

        foreach ($searchRequests as $searchRequest) {
            $this->closeSearchRequest($searchRequest);
        }
    }




}
