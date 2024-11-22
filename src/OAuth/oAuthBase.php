<?php namespace MinistryPlatformAPI\OAuth;

use GuzzleHttp\Client;

class oAuthBase
{
    /**
     * Client ID from MP for API Client you are connecting to
     * @var null
     */
    protected $mpClientId = null;

    /**
     * Client Secret from MP for the API client you are connecting to
     * @var null
     */
    protected $mpClientSecret = null;

    /**
     * For authorization Code grant types.  Redirect URL for authorization
     * @var null
     */
    protected $mpRedirectURL = null;

    /**
     * oAuth Discovery URL - provides available endpoints
     * @var null
     */
    protected $oAuthDiscoveryUrl = null;

    /**
     * URL to request authorization - Authorization Code grants
     * @var null
     */
    protected $authorization_endpoint = null;

    /**
     * URL to get a token
     * @var null
     */
    protected $token_endpoint = null;

    /**
     * URL to end an oAuth Session
     * @var null
     */
    protected $end_session_endpoint = null;

    /**
     * Endpoint for logged in user information
     * @var null
     */
    protected $userinfo_endpoint = null;

    /**
     * Unused - java web token endpoint
     * @var null
     */
    protected $jwks_uri = null;

    /**
     * List of scopes supported by this oAuth server
     * @var null
     */
    protected $scopes_supported = null;

    /**
     * Scope being used in API calls
     * @var null
     */
    public $scope = null;

    /**
     * Holding place for error message.
     * @var
     */
    protected $errorMessage;

    /**
     * Fields used by Guzzle to call endpoints
     */
    protected $oAuthFields = null;
    protected $fieldCount = null;

    protected $headers = null;

    /**
     * for caching of credentials in multi-tenant system.
     * Set this to an account name (mygcc, for example) to 
     * differentiate the different accounts using the system
     */
    protected $accountName = 'www';

    /**
     * Query discovery endpoint for available resources and capabilities
     * @return $this|bool
     */
    protected function endpointDiscovery()
    {
        // Discover the endpoints
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->get($this->oAuthDiscoveryUrl, [
                'curl' => $this->setDiscoveryCurlopts(),
            ]);

            // Parse the response
            $this->parseDiscoveryResponse($response);

        } catch (\GuzzleException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * Parse the response from the discovery endpoint and save the URIs
     *
     * @param $response
     */
    private function parseDiscoveryResponse($response)
    {
        $body = json_decode($response->getBody(), true);

        $this->authorization_endpoint = $body['authorization_endpoint'];
        $this->token_endpoint = $body['token_endpoint'];
        $this->end_session_endpoint = $body['end_session_endpoint'];
        $this->userinfo_endpoint = $body['userinfo_endpoint'];
        $this->jwks_uri = $body['jwks_uri'];

        $this->scopes_supported = $body['scopes_supported'];
    }

    /**
     * Get API configuration parameters.
     *
     * Called from the constructor
     */
    protected function getCongfigParameters()
    {
        $this->apiEndpoint = config('mp-api-wrapper.MP_API_ENDPOINT');
        $this->oAuthDiscoveryUrl = config('mp-api-wrapper.MP_OAUTH_DISCOVERY_ENDPOINT');
        $this->mpClientId = config('mp-api-wrapper.MP_CLIENT_ID');
        $this->mpClientSecret = config('mp-api-wrapper.MP_CLIENT_SECRET');
        $this->scope = config('mp-api-wrapper.MP_API_SCOPE');
        $this->mpRedirectURL = config('mp-api-wrapper.MP_OAUTH_REDIRECT_URL');

    }


    /**
     * CURLOPTS for a discovery request
     *
     * @return array
     */
    protected function setDiscoveryCurlopts()
    {
        $curlopts = [
            CURLOPT_POST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;

    }

    /**
     * CURLOPTS for an authentication request
     *
     * @return array
     */
    protected function setOauthCurlopts()
    {
        $curlopts = [
            CURLOPT_POST => $this->fieldCount,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;
    }

    /**
     * Set the cUrl Options for a get request
     *
     * @return array
     */
    protected function setGetCurlopts()
    {
        $curlopts = [
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;
    }
}