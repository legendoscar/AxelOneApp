<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class Search
{
    private $models;
    private $searchTerms;

    public function __construct(array $models, array $searchTerms)
    {
        $this->models = $models;
        $this->searchTerms = $searchTerms;
    }

    public function search()
    {
        $results = [];

        foreach ($this->models as $modelClass => $searchableFields) {
            if ($this->searchTerms['fields'] !== null && !in_array($this->searchTerms['fields'], $searchableFields)) {
                // Skip the model if the selected field is not searchable
                continue;
            }
            $results = $this->searchInModel($modelClass, $searchableFields);
            // $results[$modelClass] = $this->searchInModel($modelClass, $searchableFields);

        }

        return $results;
    }

    private function searchInModel(string $modelClass, array $searchableFields)
    {
        $model = new $modelClass();
        $query = $model->query();

        // Handle cases where no fields are selected
        if (empty($this->searchTerms['fields'])) {
            // Use default search fields
            $searchFields = $searchableFields;
        } else {
            // Use the selected search fields
            $searchFields = $this->searchTerms['fields'];
        }

        // Apply search criteria for each search field
        $searchFields = explode(',', $this->searchTerms['fields']);
        $searchQueries = [];
        foreach ($searchFields as $searchField) {
            $searchQueries[] = function ($query) use ($searchField) {
                return [
                    $query->where($searchField, 'LIKE', '%' . $this->searchTerms['query'] . '%'),
                    // $query->whereEncrypted($searchField, 'LIKE', '%' . $this->searchTerms['query'] . '%'),
                    $query->orWhereEncrypted($searchField, 'LIKE', '%' . $this->searchTerms['query'] . '%')
                ];
            };
        }

        // Apply the combined search query
        $query->where(function ($query) use ($searchQueries) {
            foreach ($searchQueries as $searchQuery) {
                $query->orWhere($searchQuery);
                // $query->orWhereEncrypted($searchQuery);
            }
        });

        // Apply filters if provided
        // if (!empty($this->searchTerms['filters'])) {
        //     foreach ($this->searchTerms['filters'] as $filterField => $filterValue) {
        //         $query->where($filterField, $filterValue);
        //     }
        // }

        return $query->get([
            'firstname',
            'lastname',
            'username',
            'profile_url',
            'email',
            'phone_number',
            'address',
            'identification_type',
            'identification_number',
            'date_of_birth',
            'country_of_residence',
            'country_of_citizenship',
            'occupation',
            'industry',
            'is_politically_exposed',
            'income_source',
            'estimated_annual_income'
        ]);
    }



}
