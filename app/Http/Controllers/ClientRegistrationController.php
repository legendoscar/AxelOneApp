<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class ClientRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->all();
        $client = Passport::client();

        if (!$client->secret) {
            // Generate a unique name for the new client
            // $name = 'Client_' . Str::uuid();

            // Create a new client with a secret using the generated name
            $client = Passport::client()->forceFill([
                'name' => $request->name,
                'password_client' => true,
                'personal_access_client' => true,
                'redirect' => '',
                'revoked' => 0
            ])->save();
        }

        // return $client;

        return response()->json([
            'message' => 'Client has been created successfully',
            'data' => [
                'id' => $client->id,
                'secret' => $client->secret
            ]
        ], 200);
    }
}
