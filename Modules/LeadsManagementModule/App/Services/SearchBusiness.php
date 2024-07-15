<?php
namespace Modules\LeadsManagementModule\App\Services;

use App\Services\SendPulseService;
use Http;
use Illuminate\Database\Eloquent\Builder;
use Modules\LeadsManagementModule\App\Jobs\SendBusinessMatchedNotificationJob;
use Modules\LeadsManagementModule\App\Notifications\BusinessMatchedFromSearchNotification;
use Modules\LeadsManagementModule\App\Notifications\NewLeadFromSearchNotification;
use Modules\LeadsManagementModule\App\Notifications\NewLeadCreatedNotification;
use Modules\LeadsManagementModule\App\Events\NewLeadCreated;
use Illuminate\Support\Facades\Notification;
use Modules\LeadsManagementModule\App\Models\SearchRequestModel;
use Illuminate\Support\Facades\Log;
use Modules\TenantOrgModule\App\Models\OrganizationLocationsModel;
use Modules\TenantOrgModule\App\Models\OrganizationModel;
use Modules\LeadsManagementModule\App\Services\SynonymService;

class SearchBusiness
{
    private $models;
    private $searchTerms;
    private $synonymService;

    /**
     * Constructor for SearchBusiness class.
     *
     * @param array $models Array of model classes to search in.
     * @param array $searchTerms Array of search terms.
     * @param SynonymService $synonymService Instance of SynonymService.
     */
    public function __construct(array $models, array $searchTerms, SynonymService $synonymService, SendPulseService $sendPulseService)
    {
        $this->models = $models;
        $this->searchTerms = $searchTerms;
        $this->synonymService = $synonymService;
        $this->sendPulseService = $sendPulseService;
    }

    /**
     * Perform the search across specified models.
     *
     * @return array Search results.
     */
    public function search()
    {
        // Start the timer
        $startTime = microtime(true);

        $results = [];

        // Create a new search request
        $searchRequest = $this->createSearchRequest($this->searchTerms);


        foreach ($this->models as $modelClass => $searchableFields) {
            try {
                $results = $this->searchInModel($modelClass, $searchableFields);
            } catch (\Exception $e) {
                Log::error('Error searching in model', [
                    'modelClass' => $modelClass,
                    'searchableFields' => $searchableFields,
                    'exception_message' => $e->getMessage()
                ]);
            }
        }
        // Stop the timer
        $endTime = microtime(true);

        // Calculate the search duration
        $duration = $endTime - $startTime;

        // Log the duration
        Log::info('SearchBusiness execution time', ['duration' => $duration]);

        // Update search request with results count and duration
        $searchRequest->update([
            'results_count' => count($results),
            'duration' => $duration,
            'org_matched' => $results->pluck('id')->toArray()
        ]);


        // dispatch the email notification
        SendBusinessMatchedNotificationJob::dispatch($results);

        return [
            'duration' => $duration,
            'results' => $results
        ];
    }

    /**
     * Perform the search within a specific model class.
     *
     * @param string $modelClass The class name of the model to search in.
     * @param array $searchableFields Array of fields to search within the model.
     * @return \Illuminate\Support\Collection Search results.
     */
    private function searchInModel(string $modelClass, array $searchableFields)
    {
        try {
            $model = new $modelClass();
            $query = OrganizationModel::query();

            $matchFound = false;

            // Apply search criteria for organization name and bio
            if (!empty($this->searchTerms[0])) {
                $service = $this->searchTerms[0];

                // Define the regex pattern for delimiters
                $pattern = '/[\s.,\-?"\'!]+/';
                $splitTerms = preg_split($pattern, $service, -1, PREG_SPLIT_NO_EMPTY);

                // Get synonyms for each part of the split search term
                $synonyms = [];
                foreach ($splitTerms as $term) {
                    $synonyms = array_merge($synonyms, $this->synonymService->getSynonyms($term));
                }

                // Combine the original search term, split terms, and their synonyms
                $searchTerms = array_merge([$service], $splitTerms, $synonyms);

                // Apply the search criteria using the expanded search terms
                $query->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('org_name', 'LIKE', '%' . $term . '%')
                            ->orWhere('org_bio', 'LIKE', '%' . $term . '%');
                    }
                });

                // Check if there are any matches for the search terms in the organization name or bio
                $matchFound = $query->exists();
            }

            if ($matchFound) {
                // Apply search criteria for location only if there is a match in org_name or org_bio
                if (!empty($this->searchTerms[1])) {
                    $location = $this->searchTerms[1];

                    // Define the regex pattern for delimiters
                    $pattern = '/[\s.,\-?"\'!]+/';
                    $locationTerms = preg_split($pattern, $location, -1, PREG_SPLIT_NO_EMPTY);

                    // Subquery to find matching organization IDs based on concatenated location criteria
                    $locationSubQuery = OrganizationLocationsModel::select('organization_id')
                        ->where(function ($q) use ($locationTerms) {
                            foreach ($locationTerms as $term) {
                                $q->orWhere('address', 'LIKE', '%' . $term . '%')
                                    ->orWhere('city', 'LIKE', '%' . $term . '%')
                                    ->orWhere('state', 'LIKE', '%' . $term . '%')
                                    ->orWhere('country', 'LIKE', '%' . $term . '%')
                                    ->orWhere('zipcode', 'LIKE', '%' . $term . '%');
                            }
                        });

                    // Combine the location subquery with the main query
                    $query->where(function ($q) use ($locationSubQuery) {
                        $q->whereIn('id', $locationSubQuery);
                    });
                }

                // Apply category filter if category is provided
                if (count($this->searchTerms) >= 3) {
                    $category = $this->searchTerms[2];

                    if (!empty($category)) {
                        $query->whereHas('businessCategories', function ($q) use ($category) {
                            $q->where('business_category_id', $category);
                        });
                    }
                }

                // Retrieve the search results with specified columns and relationships
                $results = $query->with(['businessCategories:id,name', 'businessSubUnits:id,name', 'locations'])->get([
                    'id',
                    'org_name',
                    'msg_id',
                    'org_bio',
                    'subdomain',
                    'address',
                    'city',
                    'state',
                    'zipcode',
                    'country',
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

                // Get the user who made the search request
                // $searchRequest = SearchRequestModel::find($this->searchTerms['search_request_id']);
                // $user = auth()->user();
                // $payload = [
                //     'searchTerm' => $this->searchTerms['query'],
                //     'searchFields' => $searchFields
                // ];

                // // Send notification to each matched business
                // foreach ($results as $business) {
                //     // Notification::send($business, new BusinessMatchedFromSearchNotification($user, $this->searchTerms['query'], $searchFields, $business));
                //     // Log the response for debugging purposes
                //     Log::info('Webhook sent', [
                //         'data' => $payload,
                //         'business' => [
                //             'id' => $business->id,
                //             'name' => $business->org_name
                //         ]
                //         // 'response' => response()->body(),
                //     ]);
                //     $business->notify(new NewLeadFromSearchNotification($user, $payload));

                //     // broadcast(new NewLeadCreated($user, $payload))->toOthers();
                // }


                // $this->sendPulseService->sendEmail($user->email, 'Email Verification', $htmlContent);

                // Notify the matched businesses via email & webhook
                // $this->sendEmailNotification($business);
                // $this->sendWebhookNotification($business);

                return $results;
            } else {
                // Return an empty collection if no matches are found in org_name or org_bio
                return collect([]);
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the search process
            Log::error('Error searching in model', [
                'modelClass' => $modelClass,
                'searchableFields' => $searchableFields,
                'exception_message' => $e->getMessage()
            ]);

            // Return an empty collection on exception
            return collect([]);
        }
    }

    /**
     * Send email notification to matched business.
     *
     * @param mixed $business The business instance to send notification to.
     * @return void
     */
    private function sendEmailNotification($business)
    {
        try {
            $org_name = $business->org_name;
            $org_id = $business->id;
            $url = "https://fyndah.com/businessDashboard/{$org_id}/{$org_name}/search-request";
            $htmlContent = view('emails.business.matched', ['url' => $url, 'org_name' => $org_name])->render();

            //Sending email notification using SendPulseService
            $this->sendPulseService->sendEmail(
                $business->email,
                'You have been matched in a search',
                $htmlContent
            );

            // Log successful email notification
            Log::info('Email sent to business', [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->org_name
                ]
            ]);
        } catch (\Exception $e) {
            // Log error if sending email fails
            Log::error('Error sending email notification', [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->org_name
                ],
                'exception_message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Send webhook notification to matched business.
     *
     * @param mixed $business The business instance to send notification to.
     * @return void
     */
    private function sendWebhookNotification($business)
    {
        try {
            // Example payload for webhook notification
            $payload = [
                'business_id' => $business->id,
                'org_name' => $business->org_name,
                'search_term' => $this->searchTerms[0],
            ];

            // Example code: Sending HTTP POST request to webhook endpoint
            // $response = Http::post('https://your-react-webhook-endpoint.com', $payload);

            // Example: Logging webhook notification success
            // if ($response->successful()) {
            //     Log::info('Webhook notification sent to business', [
            //         'business' => [
            //             'id' => $business->id,
            //             'name' => $business->org_name
            //         ]
            //     ]);
            // } else {
            //     Log::error('Failed to send webhook notification to business', [
            //         'business' => [
            //             'id' => $business->id,
            //             'name' => $business->org_name
            //         ],
            //         'response' => $response->body()
            //     ]);
            // }
        } catch (\Exception $e) {
            // Log any exceptions that occur during webhook notification
            Log::error('Exception while sending webhook notification to business', [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->org_name
                ],
                'exception_message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create Active Search Requests.
     *
     * @param array $searchTerms The search terms to include.
     */
    private function createSearchRequest(array $searchTerms)
    {
        try {
            // getting the search filters - location & category
            $searchFilters = [];

            if (isset($searchTerms[1])) {
                $searchFilters['location'] = $searchTerms[1];
            }

            if (isset($searchTerms[2])) {
                $searchFilters['business_category'] = $searchTerms[2];
            }

            // creating the search request
            $searchRequest = SearchRequestModel::create([
                'user_id' => auth()->user()->id,
                'search_term' => $searchTerms[0],
                'search_filters' => json_encode($searchFilters),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent')
            ]);

            // set the search request status to active
            $searchRequest->setStatus('active');

            return $searchRequest;
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error creating search request', [
                'searchTerms' => $searchTerms,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);

            // Rethrow the exception
            throw new \Exception('Unable to create search request at this time. Please try again later.');
        }
    }
}
