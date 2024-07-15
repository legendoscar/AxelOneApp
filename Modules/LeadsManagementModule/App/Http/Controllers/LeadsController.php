<?php

namespace Modules\LeadsManagementModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\LeadsManagementModule\App\Models\BidsModel;
use Modules\LeadsManagementModule\App\Models\LeadsModel;

class LeadsController extends Controller
{
    // 1. Fetch leads belonging to an org - including the bid & search request info
    public function fetchLeadsByOrg()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;


        $leads = LeadsModel::where('organization_id', $organizationId)
            ->with(['bid', 'searchRequest'])
            ->get();

        return response()->json(['status' => 'success', 'data' => $leads], 200);
    }

    // 2. Fetch leads of which a particular user made the search
    public function fetchLeadsByUser($userId)
    {
        $leads = LeadsModel::whereHas('searchRequest', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['bid', 'searchRequest'])->get();

        return response()->json(['status' => 'success', 'data' => $leads], 200);
    }

    // 3. Marking a lead with various status labels using the laravel-model-status
    public function markLeadStatus(Request $request, $id)
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        $lead = LeadsModel::where('id', $id)->where('organization_id', $organizationId)->first();

        if (!$lead) {
            return response()->json(['error' => 'Lead not found or you do not have access'], 404);
        }

        $request->validate([
            'status' => 'required|string'
        ]);

        $lead->setStatus($request->status);

        return response()->json(['status' => 'success', 'data' => $lead], 200);
    }

    // 4. Delete a lead
    public function deleteLead($id)
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        $lead = LeadsModel::where('id', $id)->where('organization_id', $organizationId)->first();

        if (!$lead) {
            return response()->json(['error' => 'Lead not found or you do not have access'], 404);
        }

        $lead->delete();

        return response()->json(['status' => 'success', 'message' => 'Lead deleted successfully'], 200);
    }

    // 5. Assign a lead to another user who belongs to the organization
    public function assignLeadToUser(Request $request, $id)
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        $lead = LeadsModel::where('id', $id)->where('organization_id', $organizationId)->first();

        if (!$lead) {
            return response()->json(['error' => 'Lead not found or you do not have access'], 404);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $newUser = User::where('id', $request->user_id)->where('organization_id', $organizationId)->first();

        if (!$newUser) {
            return response()->json(['error' => 'User not found in the organization'], 404);
        }

        $lead->user_id = $request->user_id;
        $lead->save();

        return response()->json(['status' => 'success', 'data' => $lead], 200);
    }

    // Helper method to automatically create leads based on conditions
    public function checkAndCreateLeads()
    {
        $bids = BidsModel::with(['searchRequest'])
            ->where('status', 'closed')
            ->orWhere('created_at', '<', now()->subHours(6))
            ->get();

        foreach ($bids as $bid) {
            if ($bid->searchRequest->bids()->count() >= 5 || $bid->status == 'closed') {
                DB::beginTransaction();

                try {
                    $lead = LeadsModel::create([
                        'search_request_id' => $bid->searchRequest->id,
                        'bid_id' => $bid->id,
                        'organization_id' => $bid->organization_id,
                        'user_id' => $bid->searchRequest->user_id,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error creating lead: ' . $e->getMessage());
                }
            }
        }
    }

}
