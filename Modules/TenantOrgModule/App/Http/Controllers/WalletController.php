<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;




class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function deposit(Request $request)
    {

        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|integer',
        ]);

        $org_id = auth()->user()->organization_id;

        $organization = OrganizationModel::where('id', $org_id)
            ->first();


        $organization->deposit($validatedData['amount']);

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $organization->balance
        ], 201);
    }

    public function withdraw(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'org_id' => 'required|',
            'amount' => 'required|integer',
        ]);

        $organization = OrganizationModel::where('id', $validatedData['org_id'])
            ->orWhere('org_name', $validatedData['org_id'])
            ->first();

        $organization->withdraw($validatedData['amount']);

        return response()->json([
            'message' => 'Withdrawal successful',
            'balance' => $organization->balance
        ]);
    }

    public function balance(Request $request)
    {

        $org_id = auth()->user()->organization_id;

        $organization = OrganizationModel::where('id', $org_id)
            ->orWhere('org_name', $org_id)
            ->first();

        return response()->json([
            'balance' => $organization->balance
        ]);
    }

    public function balanceAtDate(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'date' => 'required|date',
        ]);

        $org_id = auth()->user()->organization_id;


        $organization = OrganizationModel::where('id', $org_id)
            ->first();

        $date = Carbon::parse($validatedData['date']);

        $balance = $organization->wallet->transactions()
            ->where('created_at', '<=', $date)
            ->sum('amount');

        return response()->json([
            'balance' => $balance
        ]);
    }

    public function transactions(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $org_id = auth()->user()->organization_id;

        $organization = OrganizationModel::where('id', $org_id)
            // ->orWhere('org_name', $validatedData['org_id'])
            ->first();

        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);

        $transactions = $organization->wallet->transactions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get(['uuid', 'type', 'amount', 'created_at']);

        return response()->json([
            'message' => 'succcess',
            'txns' => $transactions
        ]);
    }

    public function allTransactions(Request $request)
    {
        // Validate the request data

        $org_id = auth()->user()->organization_id;

        $organization = OrganizationModel::where('id', $org_id)
            // ->orWhere('org_name', $validatedData['org_id'])
            ->first();

        $transactions = $organization->wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->get(['uuid', 'type', 'amount', 'created_at']);

        return response()->json($transactions);
    }



}
