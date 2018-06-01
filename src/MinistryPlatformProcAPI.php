<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;


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

    private $errorMessage = null;

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

        $this->errorMessage = null;

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
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        
        }  catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        
        } catch (Exception $e) {
            $this->errorMessage = 'Unknown Excpetion in Guzzle request';
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
        $auth = 'Authorization: ' . $this->credentials->get('token');
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

    /**
     * Return a Guzzle error message
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }
}