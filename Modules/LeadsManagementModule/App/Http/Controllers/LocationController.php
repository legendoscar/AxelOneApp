<?php

namespace Modules\LeadsManagementModule\App\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use PragmaRX\Countries\Package\Countries;
use PragmaRX\Countries\Package\Services\Config;



class LocationController extends Controller
{
    protected $service;
    protected $countries;
    protected $countriesService;



    public function __construct()
    {
        $this->countriesService = new Countries(new Config());

    }

    public function getCountries()
    {

        try {

            $countryCodes = ['US', 'GB', 'CA', 'AU', 'NG'];

            $filteredCountries = $this->countriesService->all()
                ->filter(function ($country) use ($countryCodes) {
                    return in_array($country->cca2, $countryCodes);
                })
                ->pluck('name.common', 'cca2', );

            return response()->json([
                'status' => 'success',
                'message' => count($filteredCountries) . ' countries returned successfully',
                'countries' => $filteredCountries
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function getStates($countryCode)
    {

        try {

            $country = $this->countriesService->where('cca2', strtoupper($countryCode))->first();

            if (!$country) {
                return response()->json(['message' => 'Country not found'], 404);
            }

            $statesData = $country->hydrateStates()->states->pluck('name', 'postal')->toArray();

            return response()->json([
                'status' => 'success',
                'message' => count($statesData) . ' states returned successfully',
                'states' => $statesData
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getCities($countryCode, $stateName)
    {
        try {

            $cities = $this->countriesService->where('cca2', strtoupper($countryCode))->first()->hydrateCities()->cities->where('adm1name', ucfirst($stateName));

            $cityNames = $cities->map(function ($city) {
                return [
                    'name' => $city->name
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => count($cityNames) . ' cities returned successfully',
                'states' => $cityNames->values()
            ]);

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

