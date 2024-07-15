<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Tenant;
use Illuminate\Support\Facades\DB;



class OrgFetchProfileController extends Controller
{

    /**
     * Fetch Org Profile.
     */
    public function fetchOrgProfile($org_key)
    {

        $org_key = request()->org_key;

        // First, try to find an organization by ID
        $org = OrganizationModel::with(['businessCategories:id,name', 'businessSubUnits:id,name', 'locations'])->find($org_key, [
            'id',
            'org_name',
            'logo',
            'cover_image',
            'org_bio',
            'subdomain',
            'phone',
            'email',
            'website',
            'industry',
            'size',
            'location',
            'products',
            'services',
            'business_hours',
            'website_social_media',
            'contact_info',
            'reviews_ratings',
            'pricing',
            'certifications_accreditations',
            'languages_spoken',
            'payment_methods',
            'nearby_landmarks',
            'parking_info',
            'pet_policy',
            'dress_code',
            'special_instructions',
            'accessibility',
            'events_promotions',
            'cancellation_policy',
            'environmental_practices',
            'awards_nominations',
            'user_generated_contents',

        ]);

        // If no organization is found, try to find by name
        if (!$org) {
            $org = OrganizationModel::with(['businessCategories:id,name', 'businessSubUnits:id,name', 'locations'])
                ->where('org_name', $org_key)
                // ->orWhere('subdomain', $org_key)
                ->first([
                    'id',
                    'org_name',
                    'org_bio',
                    'subdomain',
                    'phone',
                    'email',
                    'website',
                    'industry',
                    'size',
                    'location',
                    'products',
                    'services',
                    'business_hours',
                    'website_social_media',
                    'contact_info',
                    'reviews_ratings',
                    'pricing',
                    'certifications_accreditations',
                    'languages_spoken',
                    'payment_methods',
                    'nearby_landmarks',
                    'parking_info',
                    'pet_policy',
                    'dress_code',
                    'special_instructions',
                    'accessibility',
                    'events_promotions',
                    'cancellation_policy',
                    'environmental_practices',
                    'awards_nominations',
                    'user_generated_contents',

                ]);
        }
        // $org = OrganizationModel::where('id', $org_key)
        //     ->orWhere('org_name', $org_key)
        //     ->orWhere('subdomain', $org_key)
        //     ->first();

        if (!$org) {
            return response()->json([
                'status' => 'error',
                'exists' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'exists' => true,
            'message' => 'Organization found',
            'data' => $org
        ], 200);
    }

    /**
     * Fetch Org Locations.
     */
    // public function fetchOrgLocations($org_key)
    // {

    //     try {
    //         $org_key = request()->org_key;

    //         // First, try to find an organization by ID
    //         $org = OrganizationModel::with('locations')->findOrFail($org_key);

    //         // If no organization is found, try to find by name
    //         // if (!$org) {
    //         //     $org = OrganizationModel::with('locations')
    //         //     ->where('org_name', $org_key)
    //         //         // ->orWhere('subdomain', $org_key)
    //         //         ->firstOrFail();
    //         // }

    //         // $location = $org->location;

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => is_null($org) ? '0 locations found' : count($org) . ' locations found',
    //             'data' => [
    //                 'organization' => [
    //                     'name' => $org->org_name,
    //                     'locations' => $org
    //                 ]
    //             ]
    //         ], 200);

    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Organization not found',
    //         ], 404);
    //     }
    // }

    /**
     * Fetch Org Products
     */
    public function fetchOrgProducts($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $products = $org->products;

            return response()->json([
                'status' => 'success',
                'message' => is_null($products) ? '0 products found' : count($products) . ' products found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'products' => $products
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Services
     */
    public function fetchOrgServices($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $services = $org->services;

            return response()->json([
                'status' => 'success',
                'message' => is_null($services) ? '0 services found' : count($services) . ' services found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'services' => $services
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Business Hours
     */
    public function fetchOrgBusinessHours($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $businessHours = $org->BusinessHours;

            return response()->json([
                'status' => 'success',
                'message' => is_null($businessHours) ? '0 Business hours found' : count($businessHours) . ' Business hours found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'Business hours' => $businessHours
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org social media
     */
    public function fetchOrgSocialMedia($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $socialMedia = $org->SocialMedia;

            return response()->json([
                'status' => 'success',
                'message' => is_null($socialMedia) ? '0 social media found' : count($socialMedia) . ' social media found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'social media' => $socialMedia
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org ContactInfo
     */
    public function fetchOrgContactinfo($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $contactInfo = $org->ContactInfo;

            return response()->json([
                'status' => 'success',
                'message' => is_null($contactInfo) ? '0 Business Contact Info found' : count($contactInfo) . ' Business Contact Info found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'contact info' => $contactInfo
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Reviews Rating
     */
    public function fetchOrgReviewsRatings($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $reviewsRating = $org->ReviewsRating;

            return response()->json([
                'status' => 'success',
                'message' => is_null($reviewsRating) ? '0 reviews or ratings found' : count($reviewsRating) . ' reviews or ratings found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'reviews_ratings' => $reviewsRating
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org pricing
     */
    public function fetchOrgPricing($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $pricing = $org->pricing;

            return response()->json([
                'status' => 'success',
                'message' => is_null($pricing) ? '0 pricing found' : count($pricing) . ' pricing found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'pricing' => $pricing
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org fetchOrgCertifications
     */
    public function fetchOrgCertifications($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $certifications = $org->Certification;

            return response()->json([
                'status' => 'success',
                'message' => is_null($certifications) ? '0 business certifications found' : count($certifications) . ' business certifications found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'certifications' => $certifications
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch OrgLanguages
     */
    public function fetchOrgLanguages($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $languages = $org->Language;

            return response()->json([
                'status' => 'success',
                'message' => is_null($languages) ? '0 languages found' : count($languages) . ' languages found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'languages' => $languages
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org paymentMethod
     */
    public function fetchOrgPaymentMethods($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $paymentMethod = $org->Paymentmethod;

            return response()->json([
                'status' => 'success',
                'message' => is_null($paymentMethod) ? '0 payment method found' : count($paymentMethod) . ' payment methods found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'paymentMethod' => $paymentMethod
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org NearbyLandmark
     */
    public function fetchOrgNearbyLandmarks($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $nearbyLandmark = $org->NearbyLandmark;

            return response()->json([
                'status' => 'success',
                'message' => is_null($nearbyLandmark) ? '0 nearby landmark found' : count($nearbyLandmark) . ' nearby landmarks found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'nearbyLandmark' => $nearbyLandmark
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Parkinginfo
     */
    public function fetchOrgParkinginfo($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $parkingInfo = $org->ParkingInfo;

            return response()->json([
                'status' => 'success',
                'message' => is_null($parkingInfo) ? '0 parkingInfo found' : count($parkingInfo) . ' parkingInfo found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'parkingInfo' => $parkingInfo
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org petPolicy
     */
    public function fetchOrgPetPolicy($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $petPolicy = $org->PetPolicy;

            return response()->json([
                'status' => 'success',
                'message' => is_null($petPolicy) ? '0 petPolicy found' : count($petPolicy) . ' petPolicy found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'petPolicy' => $petPolicy
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Dress Code
     */
    public function fetchOrgDressCode($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $dressCode = $org->DressCode;

            return response()->json([
                'status' => 'success',
                'message' => is_null($dressCode) ? '0 dressCode found' : count($dressCode) . ' dressCode found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'dressCode' => $dressCode
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org specialInstruction
     */
    public function fetchOrgSpecialInstructions($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $specialInstruction = $org->SpecialInstruction;

            return response()->json([
                'status' => 'success',
                'message' => is_null($specialInstruction) ? '0 specialInstruction found' : count($specialInstruction) . ' specialInstruction found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'specialInstruction' => $specialInstruction
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org Accessibility
     */
    public function fetchOrgAccessibility($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $accessibility = $org->Accessibility;

            return response()->json([
                'status' => 'success',
                'message' => is_null($accessibility) ? '0 accessibility found' : count($accessibility) . ' accessibility found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'accessibility' => $accessibility
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org eventPromotion
     */
    public function fetchOrgEvents($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $eventPromotion = $org->EventPromotion;

            return response()->json([
                'status' => 'success',
                'message' => is_null($eventPromotion) ? '0 eventPromotion found' : count($eventPromotion) . ' eventPromotion found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'eventPromotion' => $eventPromotion
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org cancellationPolicy
     */
    public function fetchOrgCancellation($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $cancellationPolicy = $org->CancellationPolicy;

            return response()->json([
                'status' => 'success',
                'message' => is_null($cancellationPolicy) ? '0 cancellationPolicy found' : count($cancellationPolicy) . ' cancellationPolicy found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'cancellationPolicy' => $cancellationPolicy
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org EnvironmentalPractices
     */
    public function fetchOrgEnvironmental($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $environmentalPractices = $org->EnvironmentalPractices;

            return response()->json([
                'status' => 'success',
                'message' => is_null($environmentalPractices) ? '0 environmentalPractices found' : count($environmentalPractices) . ' environmentalPractices found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'environmentalPractices' => $environmentalPractices
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org AwardsNominations
     */
    public function fetchOrgAwards($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $awardsNominations = $org->AwardsNominations;

            return response()->json([
                'status' => 'success',
                'message' => is_null($awardsNominations) ? '0 awardsNominations found' : count($awardsNominations) . ' awardsNominations found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'awardsNominations' => $awardsNominations
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

    /**
     * Fetch Org AwardsNominations
     */
    public function fetchOrgUserGeneratedContents($org_key)
    {

        try {
            $org_key = request()->org_key;

            // First, try to find an organization by ID
            $org = OrganizationModel::find($org_key);

            // If no organization is found, try to find by name
            if (!$org) {
                $org = OrganizationModel::where('org_name', $org_key)
                    // ->orWhere('subdomain', $org_key)
                    ->firstOrFail();
            }

            $userGeneratedContents = $org->UserGeneratedContents;

            return response()->json([
                'status' => 'success',
                'message' => is_null($userGeneratedContents) ? '0 userGeneratedContent found' : count($userGeneratedContents) . ' userGeneratedContents found',
                'data' => [
                    'organization' => [
                        'name' => $org->org_name,
                        'userGeneratedContents' => $userGeneratedContents
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organization not found',
            ], 404);
        }
    }

}
