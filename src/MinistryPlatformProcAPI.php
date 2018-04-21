<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;
use MinistryPlatformAPI\MPoAuth;

class MinistryPlatformProcAPI
{
    use MPoAuth;
    
    /**
     *  parameters for calling procedures
     *
     */
    protected $procName = null;
    protected $procInput = null;

    /**
     * Stuff needed to execute the request
     *
     */
    private $apiEndpoint = null;
    private $headers;

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
    public function proc($procName)
    {
        $this->procName = $procName;

        return $this;
    }

    public function procInput($procInput)
    {
        $this->procInput = json_encode($procInput, true);

        return $this;
    }

    /**
     * Execute the stored procedure
     *
     * @return mixed
     */
    public function exec()
    {
        // Set the endpoint
        // $this->buildEndpoint();

        // Set the header
        $this->buildHttpHeader();

        // Get all of the results 1000 at a time
        return  $this->sendData();

    }

    /**
     * Send the request
     *
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendData() {

        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $this->buildHttpHeader();

        // Send the request
        $client = new Client(); //GuzzleHttp\Client
        
        try {

            $response = $client->request('POST', $endpoint, [
                'headers' => $this->headers,                
                'body' => $this->procInput,
                'curl' => $this->setPostCurlopts(),
            ]);

        } catch (\GuzzleException $e) {
            print_r($e->getResponse()->getBody()->getContents());
            return false;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            echo $e->getResponse()->getBody()->getContents();
            return false;
        }

        return $results = json_decode($response->getBody(), true);
    }

  
    /**
     * Construct the API Endpoint for the request
     *
     * @return string
     */
    private function buildEndpoint()
    {
        return $this->apiEndpoint . '/procs/' . $this->procName . '/';
    }
   
    private function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->token_type . ' ' . $this->access_token;
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];

    }
   
    /**
     * Set the cUrl Options for a POST request
     *
     * @return array
     */
    private function setPostCurlopts()
    {

        $curlopts = [
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $this->procInput,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;
    }  
}