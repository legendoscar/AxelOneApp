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
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTransaction;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use Unicodeveloper\Paystack\Facades\Paystack;




class PaystackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function initializePayment(Request $request)
    {

        $request->validate([
            'amount' => 'required|numeric|min:' . OrganizationModel::MIN_AMOUNT,
        ], [
            'amount.min' => 'The minimum amount is N' .OrganizationModel::MIN_AMOUNT
        ]);

        $user = auth()->user();
        $token = $request->bearerToken(); // Extract the token

        $organization_name = OrganizationModel::where('id', $user->organization_id)->value('org_name');

        $paymentData = [
            'email' => $user->email,
            'amount' => $request->amount * 100, // Convert to kobo
            'reference' => Paystack::genTranxRef(),
            'callback_url' => route('paystack.callback'),
            'metadata' => [
                'organization_id' => $user->organization_id,
                'organization_name' => $organization_name,
                'user_id' => $user->id,
                'token' => $token, // Pass the token as metadata
                'custom_fields' => [
                    [
                        'display_name' => "Payment for",
                        'variable_name' => "payment_for",
                        'value' => "Business Wallet Credit"
                    ]
                ]
            ]
        ];

        try {
            $response = Paystack::getAuthorizationUrl($paymentData);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment successfully initialized. Use the URL below to complete the payment!',
                'data' => [
                    'payment_url' => $response,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to create payment URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function verifyPayment(Request $request)
    {

        \Log::info('Verify Payment Request:', $request->all());

        $request->validate([
            'reference' => 'required|string',
            'trxref' => 'required|string',
        ]);

        try {
            $paymentDetails = Paystack::getPaymentData();
            $token = $paymentDetails['data']['metadata']['token']; // Retrieve the token from metadata

            // Authenticate user with token
            auth()->setToken($token)->authenticate();

            \Log::info('Authenticated User:', ['user' => auth()->user()]);


            $user = auth()->user();
            $organization_name = OrganizationModel::where('id', $user->organization_id)->value('org_name');
            $organization_id = $user->organization_id;
            $orgName = $organization_name;

            if ($paymentDetails['status'] && $paymentDetails['data']['status'] === 'success') {

                \Log::info('Organization Details:', ['organization' => $user->organization]);

                $amount = $paymentDetails['data']['amount'] / 100; // Convert back to naira

                // Credit the organization's wallet
                $organization = OrganizationModel::where('id', $organization_id)->first();
                $organization->deposit($amount, [
                    'transaction_type' => 'credit',
                    'description' => 'Payment via Paystack',
                    'created_by' => $user->id
                ]);

                \Log::info('Wallet Credited:', ['organization_id' => $organization_id, 'amount' => $amount]);


                // Construct the external URL with variables
                // $url = "https://fyndah.com/businessDashboard/{$organization_id}/{$orgName}/wallet";
                $url = "https://fyndah.com/businessDashboard/{$organization_id}/{$orgName}/onsuccess";

                // Redirect to the external URL
                return redirect()->away($url);
            }

            // \Log::error('Payment Verification Failed:', ['message' => $e->getMessage()]);

            $url = "https://fyndah.com/businessDashboard/{$organization_id}/{$orgName}/onfailed";

            // Redirect to the external URL
            return redirect()->away($url);
            // return response()->json(['error' => 'Payment was not successful.'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment verification failed.', 'message' => $e->getMessage()], 500);
        }
    }


}
