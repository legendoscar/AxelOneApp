<?php

namespace Modules\LeadsManagementModule\App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class LocationService
{
    protected $baseUrl;
    protected $apiKey;
    protected $tokenEndpoint;

    public function __construct()
    {
        $this->apiKey = $this->getToken();
        $this->baseUrl = env('LOCATION_API_BASE_URL');
        $this->tokenEndpoint = env('LOCATION_TOKEN_ENDPOINT');
    }

    protected function getToken()
    {
        if (Cache::has('location_api_token')) {
            return Cache::get('location_api_token');
        }

        return $this->refreshToken();
    }

    protected function refreshToken()
    {

        // return env('LOCATION_API_TOKEN');
        $response = Http::get('https://www.universal-tutorial.com/api/getaccesstoken', [
            // necessary authentication details here
            'api-token' => "61wibuzgXx4QfVcjwei7swrY2vp3I00ZPJmui4JSRHVxN6OO3pgqSmxrWlL8WtLyw7g",
            "Accept" => "application/json",
            "user-email" => "devlegendoscar@gmail.com"

            // 'grant_type' => 'client_credentials'
        ]);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            Cache::put('location_api_token', $token, now()->addHours(100));
            // $this->updateEnvToken($token);
            return $token;
        }

        throw new \Exception('Unable to refresh token');
    }

    protected function updateEnvToken($token)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents(
                $path,
                str_replace(
                    'LOCATION_API_KEY=' . env('LOCATION_API_KEY'),
                    'LOCATION_API_KEY=' . $token,
                    file_get_contents($path)
                )
            );
        }

        Config::set('location.api_key', $token);
    }

    public function getCountries()
    {
        $allCountries = $this->makeRequest('GET', 'countries');

        $specificCountries = array_filter($allCountries, function ($country) {
            return in_array($country['country_name'], ['United States', 'United Kingdom', 'Canada', 'Australia', 'Nigeria']);
        });

        return array_values($specificCountries);
    }

    public function getStates($country)
    {
        return $this->makeRequest('GET', "states/$country");
    }

    public function getCities($state)
    {
        return $this->makeRequest('GET', "cities/$state");
    }

    private function makeRequest($method, $uri)
    {
        // Ensure the token is fresh before making a request
        $this->apiKey = $this->getToken();

        $response = Http::withHeaders([
                    'Authorization' => "Bearer $this->apiKey",
                    'Accept' => 'application/json'
                ])->$method("$this->baseUrl/$uri");

        if ($response->status() == 401) {
            // If unauthorized, try to refresh the token
            $this->apiKey = $this->refreshToken();

            // Retry the request with the new token
            $response = Http::withHeaders([
                        'Authorization' => "Bearer $this->apiKey",
                        'Accept' => 'application/json'
                    ])->$method("$this->baseUrl/$uri");
        }

        return $response->json();

    }
}
