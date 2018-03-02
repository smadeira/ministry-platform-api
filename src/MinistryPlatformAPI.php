<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;

class MinistryPlatformAPI
{

    /**
     * parameters to be used for the Tables API calls
     *
     * @var null
     */
    public $tableName = null;
    public $select = null;
    public $filter = null;
    public $orderby = null;

    // Row data for update requests
    public $records = null;

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


    private $apiEndpoint = null;
    private $headers;

    private $oAuthFields = null;
    private $fieldCount = null;

    /**
     * oAuth Token Request Results
     *
     * @var null
     */
    private $access_token = null;
    private $token_type = null;
    private $scope = null;
    private $expires_in = null;

    /**
     * Set basic variables.
     *
     * MinistryPlatformAPI constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }



    /**
     * Set the table for the GET request
     *
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->tableName = $table;

        return $this;
    }

    public function record($recordID)
    {
        $this->recordID = $recordID;

        return $this;
    }

    /**
     * Set the Select clause for the GET Request
     *
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Set the filter clause for the GET request
     *
     * @param $filter
     * @return $this
     */
    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Set the order by clause for the GET request
     *
     * @param $order
     * @return $this
     */
    public function orderBy($order)
    {
        $this->orderby = $order;

        return $this;
    }

    /**
     * Set the records
     * @param $records
     * @return $this
     */
    public function records($records)
    {
        $this->records = $records;

        return $this;
    }


    /**
     * Execute the GET request using defined parameters
     *
     * @return mixed
     */
    public function get()
    {
        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $auth = 'Authorization: ' . $this->token_type . ' ' . $this->access_token;
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->request('GET', $endpoint, [
                'headers' => $this->headers,
                'query' => ['$select' => $this->select,
                    '$filter' => $this->filter,
                    '$orderby' => $this->orderby],
                'curl' => $this->setGetCurlopts(),
            ]);

        } catch (GuzzleException $e) {
            print_r($e->getResponse()->getBody()->getContents());
            return false;

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getResponse()->getBody()->getContents();
            return false;
        }

        return $results = json_decode($response->getBody(), true);
    }

    /**
     * Get only the first returned result
     *
     * @return bool
     */
    public function first()
    {
        if ($results = $this->get()) {
            return $results[0];
        }

        return false;
    }

    /**
     * Execute a PUT request to update existing records
     * @return bool|mixed
     */
    public function put()
    {

        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $auth = 'Authorization: ' . $this->token_type . ' ' . $this->access_token;
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        echo $this->records . "\n";

        try {

            $response = $client->request('PUT', $endpoint, [
                'headers' => $this->headers,
                'curl' => $this->setPutCurlopts(),
            ]);

        } catch (GuzzleException $e) {
            print_r($e->getResponse()->getBody()->getContents());
            return false;

        } catch (GuzzleHttp\Exception\ClientException $e) {
            echo $e->getResponse()->getBody()->getContents();
            return false;
        }

        return $results = json_decode($response->getBody(), true);
    }

    /**
     * Performs discovery request and gets the token
     * @return $this|bool
     */
    public function authenticate()
    {
        // Get the Discovery URI
        if (! $this->endpointDiscovery()) return false;

        // Request a token
        if (! $this->getToken() ) return false;

        return $this;
    }

    /**
     * Construct the API Endpoint for the request
     *
     * @return string
     */
    private function buildEndpoint()
    {
        return $this->apiEndpoint . '/tables/' . $this->tableName . '/';
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

        $this->access_token = $body['access_token'];
        $this->token_type = $body['token_type'];
        // $this->scope = $body['scope'];
        $this->expires_in = $body['expires_in'];
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
     * Get the Access token
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
     * Initialize the class.  Called from the constructor
     *
     */
    private function initialize()
    {
        // Read the config file
        $this->setParams();

    }

    /**
     * Get connection parameters from env file
     */
    private function setParams()
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
     * Set the cUrl Options for a get request
     *
     * @return array
     */
    private function setGetCurlopts()
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

    /**
     * Set the cUrl Options for a PUT request
     *
     * @return array
     */
    private function setPutCurlopts()
    {

        $curlopts = [
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $this->records,
            CURLOPT_HEADER => 0,
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

