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



class FlutterwaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function initiatePayment(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:' . OrganizationModel::MIN_AMOUNT,
        ], [
            'amount.min' => 'The minimum amount is N' . OrganizationModel::MIN_AMOUNT
        ]);

        $amount = $validatedData['amount'];
        $organization_id = auth()->user()->organization_id;
        $currency = env('FLUTTERWAVE_CURRENCY', 'NGN'); // Default to NGN if not set

        // Define retry middleware
        $retryMiddleware = Middleware::retry(
            function ($retries, RequestInterface $request, ResponseInterface $response = null, RequestException $exception = null) {
                // Retry up to 5 times
                if ($retries >= 5) {
                    return false;
                }

                // Retry on server errors and 429 too many requests
                if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504])) {
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff with jitter
                return 1000 * pow(2, $retries) + random_int(0, 1000);
            }
        );

        // Create a handler stack and push the retry middleware
        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($retryMiddleware);

        // Create a Guzzle client with the handler stack
        $client = new Client(['handler' => $stack]);

        try {
            $response = $client->post('https://api.flutterwave.com/v3/payments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'tx_ref' => uniqid() . '_' . $organization_id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'redirect_url' => env('FLUTTERWAVE_REDIRECT_URL'),
                    'customer' => [
                        'email' => auth()->user()->email,
                        'phonenumber' => auth()->user()->phone,
                        'name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
                    ],
                    'customizations' => [
                        'title' => 'Payment for Lead',
                        'description' => 'Payment for lead acquisition'
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data, 201);
        } catch (RequestException $e) {
            Log::error('Flutterwave API error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Payment initiation failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    private function getCurrencyForCountry($country)
    {
        $currencyMap = [
            'USA' => 'USD',
            'Canada' => 'USD',
            'Australia' => 'USD',
            'UK' => 'GBP',
            'Nigeria' => 'NGN'
        ];



        return $currencyMap[$country] ?? 'GBP';
    }

    public function handleCallback(Request $request)
    {

        // Validate the request data
        $validatedData = $request->validate([
            'status' => 'required|',
            'tx_ref' => 'required',
            'transaction_id' => 'required',
        ]);

        $status = $validatedData['status'];
        $tx_ref = $validatedData['tx_ref'];
        $transaction_id = $validatedData['transaction_id'];

        if ($status == 'successful') {
            $client = new Client();
            $response = $client->get("https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify", [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY')
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['status'] == 'success') {
                // Assuming the tx_ref is structured as 'uniqueid_user_id'
                $tx_refParts = explode('_', $tx_ref);
                $organization_id = $tx_refParts[1];
                $amount = $data['data']['amount'];

                // Credit the organization's wallet
                // $organization = OrganizationModel::findOrFail($organization_id);
                $organization = OrganizationModel::where('id', $organization_id)
                    ->orWhere('org_name', $organization_id)
                    ->first();

                $organization->deposit($amount, [
                    'transaction_type' => 'credit',
                    'description' => 'Payment via Flutterwave',
                    'created_by' => auth()->user()->id
                ]);

                // Record the transaction
                // WalletTransaction::create([
                //     'organization_id' => $organization->id,
                //     'amount' => $amount,
                //     'transaction_type' => 'credit',
                //     'description' => 'Payment via Flutterwave',
                // ]);

                return response()->json(['message' => 'Payment successful and wallet credited.']);
            }
        }

        return response()->json(['message' => 'Payment verification failed.'], 400);
    }


    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        // Verify the webhook signature
        $signature = $request->header('verif-hash');
        if (!$signature || $signature !== env('FLUTTERWAVE_WEBHOOK_SECRET')) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        Log::info('Webhook received', $payload);

        if (isset($payload['event']) && $payload['event'] === 'charge.completed') {
            $transactionId = $payload['data']['id'];
            $amount = $payload['data']['amount'];
            $currency = $payload['data']['currency'];
            $tx_ref = $payload['data']['tx_ref'];
            $status = $payload['data']['status'];

            if ($status === 'successful') {
                // Extract organization ID from tx_ref
                $tx_refParts = explode('_', $tx_ref);
                $organization_id = $tx_refParts[1];

                $organization = OrganizationModel::find($organization_id);
                if ($organization) {
                    // Credit the organization's wallet
                    $organization->deposit($amount, [
                        'transaction_type' => 'credit',
                        'description' => 'Payment via Flutterwave',
                        'created_by' => Auth::user()->id ?? null
                    ]);

                    return response()->json(['status' => 'success', 'message' => 'Payment successful and wallet credited.'], 200);
                }
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Payment verification failed.'], 400);
    }

}
