<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;


class MinistryPlatformProcAPI extends MinistryPlatformBaseAPI
{

    /**
     *  parameters for calling procedures
     *
     */
    protected $procName = null;


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
        $this->postFields = json_encode($procInput, true);

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
    protected function sendData() {

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
                'body' => $this->postFields,
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
    protected function buildEndpoint()
    {
        return $this->apiEndpoint . '/procs/' . $this->procName . '/';
    }
   
    protected function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->credentials->get('token');
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];
    }


    /**
     * Return a Guzzle error message
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }
}