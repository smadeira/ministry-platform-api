<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;


trait MPoAuth
{

    // New oAuth stuff
    private $mpClientId = null;
    private $mpClientSecret = null;

    // Values returned in discovery
    private $oAuthDiscoveryUrl = null;
    private $authorization_endpoint = null;
    private $token_endpoint = null;
    private $end_session_endpoint = null;
    private $userinfo_endpoint = null;
    private $jwks_uri = null;
    private $scopes_supported = null;

    private $oAuthFields = null;
    private $fieldCount = null;

    /**
     * oAuth Token Request Results
     *
     * @var null
     */
    private $credentials = null;

    /**
     * Performs discovery request and gets the token
     * @return $this|bool
     */
    public function authenticate()
    {
        if (! $this->credentials) {
            $this->credentials = new Credentials;
        }

        if (! $token = $this->credentials->get()){

            // Get the Discovery URI
            if (!$this->endpointDiscovery()) return false;

            // Request a token
            if (!$this->getToken()) return false;
        }

        return $this;
    }

    /**
     * Erase the credentials
     *
     * @return bool
     */
    public function clear()
    {
        if (! $this->credentials) {
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
    private function getToken()
    {
        // Request the token
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->post($this->token_endpoint, [
                'form_params' => $this->getOauthFields(),
                'curl' => $this->setOauthCurlopts(),
            ]);

            // Get the token and type from the response
            $this->parseTokenResponse($response);

        } catch (GuzzleException $e) {

            return false;
        }

        return true;
    }

    /**
     * Field list for oAuth authentication
     *
     * @return array|null
     */
    private function getOauthFields()
    {
        $this->oAuthFields = [
            'grant_type' => 'client_credentials',
            'scope' => $this->scope,
            'client_secret' => $this->mpClientSecret,
            'client_id' => $this->mpClientId,
        ];

        $this->fieldCount = count($this->oAuthFields);

        return $this->oAuthFields;
    }

    /**
     * Get tokens from authentication attempt
     *
     * @param $response
     */
    private function parseTokenResponse($response)
    {
        $body = json_decode($response->getBody(), true);

        $this->credentials->save($body);

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
     * Query discovery endpoint for available resources and capabilities
     * @return $this|bool
     */
    private function endpointDiscovery()
    {
        // Discover the endpoints
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->get($this->oAuthDiscoveryUrl, [
                'curl' => $this->setDiscoveryCurlopts(),
            ]);

            // Parse the response
            $this->parseDiscoveryResponse($response);

        } catch (GuzzleException $e) {

            return false;
        }

        return true;
    }

    /**
     * Initialize the class.  Called from the constructor
     *
     */
    private function initialize()
    {
        $this->apiEndpoint = getenv('MP_API_ENDPOINT', null);
        $this->oAuthDiscoveryUrl = getenv('MP_OAUTH_DISCOVERY_ENDPOINT', null);
        $this->mpClientId = getenv('MP_CLIENT_ID', null);
        $this->mpClientSecret = getenv('MP_CLIENT_SECRET', null);
        $this->scope = getenv('MP_API_SCOPE', null);

    }

    /**
     * CURLOPTS for an authentication request
     *
     * @return array
     */
    private function setOauthCurlopts()
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
     * CURLOPTS for a discovery request
     *
     * @return array
     */
    private function setDiscoveryCurlopts()
    {
        $curlopts = [
            CURLOPT_POST => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;

    }
}