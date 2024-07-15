<?php

namespace Modules\LeadsManagementModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use App\Models\User;
use Modules\LeadsManagementModule\App\Models\LeadsModel;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Modules\LeadsManagementModule\App\Models\BidsModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\TenantOrgModule\App\Models\OrganizationModel;



class BidsController extends Controller
{

    private function fetchOrganizationBids($status = null, Request $request)
    {
        try {

            // Validation rules for dates
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);


            // Get the authenticated user
            $user = auth()->user();

            // Ensure the user belongs to an organization
            if (!$user->organization_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User does not belong to any organization'
                ], 403);
            }

            // Fetch the user's organization
            $organization = OrganizationModel::find($user->organization_id);

            if (!$organization) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Organization not found'
                ], 404);
            }

            // Fetch all bids placed by the user's organization
            $query = BidsModel::with(['searchRequest', 'lead'])
                ->where('organization_id', $organization->id)
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

            $bids = $query->get();

            // Format the bids data
            $bids->transform(function ($bid) {
                $createdAt = $bid->getOriginal('created_at');
                $wonStatus = $bid->statuses->firstWhere('name', 'won');
                $lostStatus = $bid->statuses->firstWhere('name', 'lost');
                $wonAt = $wonStatus ? Carbon::parse($wonStatus->updated_at)->format('D, jS F Y, h:i:sA') : null;
                $lostAt = $lostStatus ? Carbon::parse($lostStatus->updated_at)->format('D, jS F Y, h:i:sA') : null;

                $response = [
                    'bid' => [
                        'bid_id' => $bid->id,
                        'bid_amount' => $bid->bid_currency . $bid->bid_amount,
                        'bid_datetime' => Carbon::parse($bid->created_at)->format('D, jS F Y, h:i:sA'),
                        'bid_datetime_ago' => Carbon::parse($bid->created_at)->diffForHumans(),
                        'bid_status' => $bid->status()->name ?? 'N/A'
                    ],
                    'search_request' => [
                        'search_request_id' => $bid->search_request_id,
                        'search_term' => $bid->searchRequest->search_term ?? 'N/A',
                        'search_filters' => $bid->searchRequest->search_filters ?? 'N/A',
                        'search_datetime' => Carbon::parse($bid->searchRequest->created_at)->format('D, jS F Y, h:i:sA'),
                        'search_datetime_ago' => Carbon::parse($bid->searchRequest->created_at)->diffForHumans(),
                        'ip_address' => $bid->searchRequest->ip_address ?? 'N/A',
                        'user_agent' => $bid->searchRequest->user_agent ?? 'N/A',
                        'search_request_status' => $bid->searchRequest->status()->name ?? 'N/A'
                    ],
                    'search_by' => [
                        'user_id' => $bid->searchRequest->user_id ?? 'N/A',
                        'msg_id' => $bid->searchRequest->user->msg_id ?? 'N/A',
                        'firstname' => $bid->searchRequest->user->firstname ?? 'N/A',
                        'lastname' => $bid->searchRequest->user->lastname ?? 'N/A',
                        'username' => $bid->searchRequest->user->username ?? 'N/A',
                        'email' => $bid->searchRequest->user->email,
                        'phone_number' => $bid->searchRequest->user->phone_number,

                    ]
                ];

                // Include won_at or lost_at if the bid is marked as won or lost
                if ($wonAt) {
                    $response['won_at'] = $wonAt;
                } elseif ($lostAt) {
                    $response['lost_at'] = $lostAt;
                }

                return $response;
            });

            return $bids;

        } catch (Exception $e) {
            // Log the exception
            Log::error('Error fetching organization bids: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch bids at this time'
            ], 500);
        }
    }

    /* Get active bids for the current user organization */
    public function activeOrganizationBids(Request $request)
    {

        try {
            // Call the private function to get bids
            $bids = $this->fetchOrganizationBids('active', $request);
            // Format the response
            return response()->json([
                'status' => 'success',
                'message' => count($bids) . ' bids returned',
                'data' => $bids
            ], 200);
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch bids at this time'
            ], 500);
        }
    }

    /* Get the bids history for the current user organization */
    public function getOrganizationBidsHistory(Request $request)
    {
        try {
            // Call the private function to get bids
            $bids = $this->fetchOrganizationBids(null, $request);

            // Format the response
            return response()->json([
                'status' => 'success',
                'message' => count($bids) . ' bids returned',
                'data' => $bids
            ], 200);
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch bids at this time'
            ], 500);
        }
    }

    /* Get the bids won by the current user organization */
    public function getBidsWonByOrganization(Request $request)
    {
        try {
            $status = trim('won');
            // Call the private function to get won bids
            $bids = $this->fetchOrganizationBids($status, $request);

            // Format the response
            return response()->json([
                'status' => 'success',
                'message' => count($bids) . ' ' . $status . ' bids returned',
                'data' => $bids
            ], 200);
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch ' . $status . ' bids at this time'
            ], 500);
        }
    }

    /* Get the bids lost by the current user organization */
    public function getBidsLostByOrganization(Request $request)
    {
        try {
            $status = trim('lost');
            // Call the private function to get lost bids
            $bids = $this->fetchOrganizationBids($status, $request);

            // Format the response
            return response()->json([
                'status' => 'success',
                'message' => count($bids) . ' ' . $status . ' bids returned',
                'data' => $bids
            ], 200);
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch ' . $status . ' bids at this time'
            ], 500);
        }
    }

    public function placeBid(Request $request)
    {
        $request->validate([
            'bid_amount' => 'required|numeric|min:0.01',
            'search_request_id' => 'required|integer|exists:search_requests,id',
        ], ['search_request_id.exists' => 'The Search Request ID is not found']);

        DB::beginTransaction();

        try {

            $searchRequestId = $request->input('search_request_id');
            $bid_amount = $request->input('bid_amount');
            $bid_currency = env('APP_CURRENCY');
            $max_bidders = BidsModel::MAX_BIDDERS;

            $searchRequest = SearchRequestModel::findOrFail($searchRequestId);
            $organizationId = auth()->user()->organization_id;
            $organization = OrganizationModel::findOrFail($organizationId);

            if ($searchRequest->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Search request is no longer active and open for bidding'
                ], 400);
            }

            if (is_null($searchRequest->org_matched)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This business has no access to this search request'
                ], 403);
            }


            if (!in_array($organizationId, $searchRequest->org_matched)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This business has no access to this search request'
                ], 403);
            }

            $existingBid = BidsModel::where('search_request_id', $searchRequestId)
                ->where('organization_id', $organizationId)
                ->first();

            if ($existingBid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already placed a bid on this search request.'
                ], 400);
            }

            if ($searchRequest->bids()->count() >= $max_bidders) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The Number of Bids are exceeded.'
                ], 403);
            }

            if ($organization->balance < $bid_amount) {
                return response()->json(['error' => 'Insufficient funds in the wallet'], 400);
            }

            $bid = BidsModel::create([
                'search_request_id' => $searchRequestId,
                'organization_id' => $organizationId,
                'bid_amount' => $bid_amount,
                'bid_currency' => $bid_currency,
            ]);

            $bid->setStatus('active', 'active bids');
            $organization->withdraw($bid_amount);

            if ($searchRequest->bids()->count() >= $max_bidders) {
                $searchRequest->setStatus('closed');
            }

            $this->checkAndCloseBidOnSearchRequest($searchRequest);

            DB::commit();

            // Transform the data for the response
            $createdAt = Carbon::parse($bid->created_at);
            $statusName = $bid->statuses->last()->name ?? 'N/A';

            $response = [
                'bid_id' => $bid->id,
                'bid_amount' => $bid->bid_currency . $bid->bid_amount,
                'bid_datetime' => $createdAt->format('D, jS F Y, h:i:sA'),
                'bid_datetime_ago' => $createdAt->diffForHumans(),
                'status' => $statusName,
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

            return response()->json([
                'status' => 'success',
                'message' => 'Bid successful',
                'bid' => $response
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error placing bid: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to place bid', 'message' => $e->getMessage()], 500);
        }
    }


    public function getLead($searchRequestId)
    {
        try {
            $searchRequest = SearchRequestModel::findOrFail($searchRequestId);

            // Check if the search request is closed
            if ($searchRequest->status()->name !== 'closed') {
                return response()->json(['error' => 'Search request is not closed'], 400);
            }

            $highestBid = $searchRequest->bids()->orderBy('bid_amount', 'desc')->first();

            // Check if the authenticated user's organization placed the highest bid
            if ($highestBid && $highestBid->organization_id == auth()->user()->organization_id) {
                return response()->json($searchRequest->user, 200); // Assuming the user who made the search is a relation on the SearchRequestModel
            }

            return response()->json(['error' => 'You do not have the highest bid'], 403);
        } catch (Exception $e) {
            Log::error('Error fetching lead: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch lead', 'message' => $e->getMessage()], 500);
        }
    }

    public function closeActiveBid($searchRequestId)
    {
        // Retrieve the search request
        $searchRequest = SearchRequestModel::findOrFail($searchRequestId);

        // Check if the user's organization has access to the search request
        $userOrgId = auth()->user()->organization_id;
        if (!in_array($userOrgId, $searchRequest->org_matched)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your organization does not have access to this search request'
            ], 403);
        }

        // Check if the bid should be closed due to 5 bids or over 6 hours
        $bidsCount = $searchRequest->bids()->count();
        $createdAt = $searchRequest->created_at;

        if ($bidsCount < 5 && $createdAt->diffInHours(now()) < 6) {
            return response()->json([
                'message' => 'Bid still open',
            ], 403);
        }

        // Retrieve the highest bid
        $highestBid = $searchRequest->bids()->orderBy('bid_amount', 'desc')->first();

        // Mark the search request as closed
        $searchRequest->setStatus('closed', 'closed search request');

        // Mark the highest bid as won
        $highestBid->setStatus('won', 'won the bid');

        // Create a new lead
        $lead = LeadsModel::create([
            'search_request_id' => $searchRequest->id,
            'bid_id' => $highestBid->id,
            'organization_id' => $userOrgId,
            'user_id' => $searchRequest->user_id,
        ]);

        $lead->setStatus('new', 'new lead created');

        // Notify the highest bidder or perform any other necessary actions

        return response()->json([
            'status' => 'success',
            'message' => 'Lead created successfully',
            'lead' => $lead
        ], 201);
    }


    public function getBidStatus($searchRequestId)
    {
        // return $searchRequestId;
        $searchRequest = BidsModel::findOrFail($searchRequestId);

        // Get all bids associated with the search request and their latest statuses


        $bidBtatus = $searchRequest->status;

        return response()->json([
            'status' => 'success',
            'message' => 'Bid closed',
            'highest_bidder' => $bidBtatus
        ], 201);

    }

    public function getWonBids(Request $request)
    {
        try {
            $organizationId = $request->user()->organization_id;

            // Fetch the bids won by the organization
            $wonBids = BidsModel::where('organization_id', $organizationId)
                ->currentStatus('won')
                ->with(['searchRequest.user'])
                ->get();

            // Convert bids into leads
            $leads = $wonBids->map(function ($bid) {
                return [
                    'lead_id' => $bid->id,
                    'search_term' => $bid->searchRequest->search_term,
                    'search_filters' => $bid->searchRequest->search_filters,
                    'user_info' => [
                        'user_id' => $bid->searchRequest->user->id,
                        'name' => $bid->searchRequest->user->name,
                        'email' => $bid->searchRequest->user->email,
                        'msg_id' => $bid->searchRequest->user->msg_id,
                    ],
                    'bid_amount' => $bid->bid_amount,
                    'created_at' => $bid->created_at,
                ];
            });

            return response()->json([
                'leads' => $leads
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch won bids',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllBids()
    {
        try {
            $bids = BidsModel::all();
            return response()->json([
                'status' => 'success',
                'data' => $bids
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching bids: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch bids', 'message' => $e->getMessage()], 500);
        }
    }

    public function getBid($id)
    {
        try {
            $bid = BidsModel::findOrFail($id, [
                "id",
                "search_request_id",
                "organization_id",
                "bid_amount",
                "created_at"
            ]);

            $userOrganizationId = auth()->user()->organization_id;
            if ($bid->organization_id !== $userOrganizationId) {
                return response()->json(['error' => 'You do not have permission to view this bid'], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $bid
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Bid not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching bid: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch bid', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateBid(Request $request, $id)
    {

        $request->validate([
            'bid_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();

        try {
            $bid = BidsModel::findOrFail($id, [
                "id",
                "search_request_id",
                "organization_id",
                "bid_amount",
                "created_at"
            ]);

            // return $bid->status;
            // if ($bid->status !== 'active') {
            //     return response()->json(['error' => 'The bid is not active'], 400);
            // }

            $userOrganizationId = auth()->user()->organization_id;
            if ($bid->organization_id !== $userOrganizationId) {
                return response()->json(['error' => 'You do not have permission to update this bid'], 403);
            }

            $bid->update([
                'bid_amount' => $request->input('bid_amount'),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $bid
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bid not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bid: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update bid', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteBid($id)
    {
        DB::beginTransaction();

        try {
            $bid = BidsModel::findOrFail($id);

            $userOrganizationId = auth()->user()->organization_id;
            if ($bid->organization_id !== $userOrganizationId) {
                return response()->json(['error' => 'You do not have permission to delete this bid'], 403);
            }

            $bid->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bid deleted successfully.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Bid not found or already deleted'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting bid: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete bid', 'message' => $e->getMessage()], 500);
        }
    }

    private function checkAndCloseBidOnSearchRequest($searchRequest)
    {
        if ($searchRequest->bids()->count() >= 5) {
            $searchRequest->setStatus('closed');
        }
    }


}

