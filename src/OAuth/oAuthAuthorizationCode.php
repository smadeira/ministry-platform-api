<?php namespace MinistryPlatformAPI\OAuth;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class oAuthAuthorizationCode extends oAuthBase
{
    /**
     * oAuth Token Request Results
     *
     * @var null
     */
    public $credentials = null;

    /**
     * oAuthAuthorizationCode constructor.
     */
    public function __construct()
    {
        // Get IDs and secrets, etc. for authorization
        $this->getCongfigParameters();

        // Check the session for existing credentials
        if (Session::has('creds') ){
            $creds = Session::get('creds');
            $this->credentials = unserialize($creds);

            // If credentials expired, erase them to force a new credential request
            if (! $this->credentials->isValidToken()){
                $this->credentials = null;
            }
        }
    }

    /**
     * Build the authorization code request URL
     * @return bool
     */
    public function authorizationCodeUrl()
    {
        // Get the Discovery URI
        if (!$this->endpointDiscovery()) return false;

        $url = $this->authorization_endpoint;
        $url .= '?response_type=code';
        $url .= '&client_id=' . $this->mpClientId;
        $url .= '&scope=' . $this->scope . ' openid';
        $url .= '&redirect_uri=' . $this->mpRedirectURL;

        return $url;
    }

    /**
     * Use the Authorization Code to get an Access Token
     *
     * @param $code
     * @return oAuthAuthorizationCode
     */
    public function acquireAccessToken($code)
    {
        if (! $this->credentials) {

            $this->credentials = new Credentials('authorization_code');

            // Get API endpoints
            $this->endpointDiscovery();

            // Acquire the tokens and expiration and save
            $body = $this->acquireToken($code);
            $this->credentials->set($body);

            // Get user Info
            $userInfo = $this->acquireUserInfo();
            $this->credentials->set('userInfo', $userInfo);

            // Add credentials to the session
            $creds = serialize($this->credentials);
            Session::put('creds', $creds);
        }

        return $this;
    }

    /**
     * Get user info from the OAuth server
     */
    public function acquireUserInfo()
    {
        // Request the userinfo
        $client = new Client(); //GuzzleHttp\Client

        $this->buildHttpHeader();
        $endpoint = $this->userinfo_endpoint;

        try {
            $response = $client->request('GET', $endpoint, [
                'header' => $this->headers,
                'curl' => $this->setGetCurlopts(),
            ]);

            // Get the token and type from the response
            // $this->parseTokenResponse($response);
            return json_decode($response->getBody(), true);

        } catch (\GuzzleException $e) {

            return false;
        }
    }

    /**
     * Field list for Access Token Request
     *
     * @return array|null
     */
    private function getAccessTokenFields($code)
    {
        $this->oAuthFields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->mpRedirectURL,
            'client_id' => $this->mpClientId,
            'client_secret' => $this->mpClientSecret,
        ];

        $this->fieldCount = count($this->oAuthFields);

        return $this->oAuthFields;
    }

    /**
     * Erase the credentials
     *
     * @return bool
     */
    public function clear()
    {
        if (!$this->credentials) {
            $this->credentials = new Credentials;
        }

        $this->credentials->forget();

        return true;
    }

    /**
     * Get a new Access token
     *
     * @return bool
     */
    private function acquireToken($code)
    {
        // Request the token
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->post($this->token_endpoint, [
                'form_params' => $this->getAccessTokenFields($code),
                'curl' => $this->setOauthCurlopts(),
                'synchronous' => true,
            ]);

            // Get the token and type from the response
            return json_decode($response->getBody(), true);

        } catch (\GuzzleException $e) {

            return false;
        }
    }

    protected function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->credentials->getAccessToken();
        $scope = 'Scope: ' . $this->scope;
        $this->headers =  ['Accept: application/json', 'Content-type: application/json', $auth, $scope];
        return $this->headers;

    }
}