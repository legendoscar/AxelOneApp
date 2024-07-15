<?php
namespace Modules\LeadsManagementModule\App\Services;

use GuzzleHttp\Client;

class SynonymService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getSynonyms($word)
    {
        $response = $this->client->get('https://api.datamuse.com/words', [
            'query' => [
                'ml' => $word,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return array_map(function ($item) {
            return $item['word'];
        }, $data);
    }
}
