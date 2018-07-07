<?php namespace MinistryPlatformAPI;

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


    protected $mpRedirectURL = null;


    /**
     * oAuth Discovery URL - provides available endpoints
     * @var null
     */
    protected $oAuthDiscoveryUrl = null;

    protected $authorization_endpoint = null;

    protected $token_endpoint = null;

    protected $end_session_endpoint = null;

    protected $userinfo_endpoint = null;

    protected $jwks_uri = null;


    protected $scopes_supported = null;

    /**
     * Scope used in API calls
     * @var null
     */
    public $scope = null;



    protected $errorMessage;


    protected $oAuthFields = null;
    protected $fieldCount = null;


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

        return $results = json_decode($response->getBody(), true);
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
    protected function initialize()
    {
        $this->apiEndpoint = getenv('MP_API_ENDPOINT', null);
        $this->oAuthDiscoveryUrl = getenv('MP_OAUTH_DISCOVERY_ENDPOINT', null);
        $this->mpClientId = getenv('MP_CLIENT_ID', null);
        $this->mpClientSecret = getenv('MP_CLIENT_SECRET', null);
        $this->scope = getenv('MP_API_SCOPE', null);
        $this->mpRedirectURL = getenv('MP_OAUTH_REDIRECT_URL', null);
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
}