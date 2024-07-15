<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Facebook\Facebook;
use Illuminate\Http\Request;

class FacebookInterestsController extends Controller
{
    protected $fb;

    public function __construct(Facebook $fb)
    {
        $this->fb = $fb;
    }

    public function fetchInterests()
    {
        $this->fb->setDefaultAccessToken(env('FACEBOOK_APP_ID') . '|' . env('FACEBOOK_APP_SECRET'));

        try {
            $response = $this->fb->get('/interests?fields=id,name,audience_size');
            dd($response);
            $interests = $response->getGraphEdge()->asArray();
            return response()->json(['interests' => $interests]);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return response()->json(['error' => 'Graph API returned an error: ' . $e->getMessage()], 500);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => 'Facebook SDK returned an error: ' . $e->getMessage()], 500);
        }
    }
}
