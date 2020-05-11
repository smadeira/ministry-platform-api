<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;

class MinistryPlatformFilesAPI extends MinistryPlatformBaseAPI
{
    
    /**
     * table affected when GET and POST to a table/recordID combo
     *
     * @var null
     */
    protected $tableName = null;
    
    /**
     * recordID affected when GET and POST to a table/recordID combo
     *
     * @var null
     */
    protected $recordID = null;
    
    /**
     * Flag to indicate get/set file as default file for a record. Applicable to GET and POST by table/recordID combo
     *
     * @var null
     */
    protected $default = null;
    
    /**
     * fileID for GET/DELETE/PUT based on files table primary key
     *
     * @var null
     */
    protected $fileID = null;
    
    /**
     * global unique identifier for GET/DELETE/PUT
     *
     * @var null
     */
    protected $uniqueID = null;
    
    /**
     * Boolean to indicate that fileID and uniqueID requests are for metadata only
     *
     * @var false | defailts to non-metadata request
     */
    protected $metadata = false;
    
    /**
     * Guzzle error message is stored here
     *
     * @var null
     */
    protected $errorMessage = null;
    
    /**
     * Set the table for the GET POST request
     *
     * @param $table
     *
     * @return $this
     */
    public function table($table)
    {
        $this->tableName = $table;
        
        return $this;
    }
    
    /**
     * Set the recordID for GET/POST request
     *
     * @param $recordID
     *
     * @return $this
     */
    public function recordID($recordID)
    {
        $this->recordID = $recordID;
        
        return $this;
    }
    
    /**
     * Set the default image flag for GET requests
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default)
    {
        $this->default = $default;
        
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
        $this->buildHttpHeader();
        
        // Get all of the results
        return $this->getFiles($endpoint);
    }
    
    /**
     * Execute the GET Request with provided parameters
     *
     * @param $endpoint
     *
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getFiles($endpoint)
    {
        // Send the request
        $client = new Client(); //GuzzleHttp\Client
        
        try {
            $response = $client->request('GET', $endpoint, [
                    'headers' => $this->headers,
                    'curl' => $this->setGetCurlopts(),
            ]);
        } catch (\GuzzleException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();
            return false;
        }
        
        $this->reset();
        return json_decode($response->getBody(), true);
    }
    
    /**
     * Construct the API Endpoint for the request based on the previously supplied parameters
     *
     * @return string
     */
    protected function buildEndpoint()
    {
        if ( $this->isTableRecordRequest() ) {
            $endpoint = $this->authorization->apiEndpoint . '/files/' . $this->tableName . '/' . $this->recordID;
        } else if ( $this->isFileIDRequest() ) {
            $endpoint = $this->authorization->apiEndpoint . '/files/' . $this->fileID;
            $endpoint .= $this->metadata ? '/metadata' : '';
        } else if ( $this->isUniqueIDRequest() ) {
            $endpoint = $this->authorization->apiEndpoint . '/files/' . $this->uniqueID;
            $endpoint .= $this->metadata ? '/metadata' : '';
        } else {
            // We have an error or inconsistent parameters in our setup somewhere
            return false;
        }
        
        return $endpoint;
    }
    
    protected function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->authorization->credentials->getAccessToken();
        $scope = 'Scope: ' . $this->authorization->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];
        
        return $this->headers;
    }
    
    /**
     * Return the error message from the request
     *
     * @return null
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }
    
    /**
     * Reset all parameters after an API call
     */
    private function reset()
    {
        $this->uniqueID = null;
        $this->tableName = null;
        $this->recordID = null;
        $this->default = null;
        $this->fileID = null;
        $this->uniqueID = null;
        $this->metadata = false;
    }
    
    /**
     * Check for values in table name and record ID required table/record URLs
     *
     * @return bool
     */
    private function isTableRecordRequest()
    {
        return isset($this->tableName, $this->recordID);
    }
    
    /**
     * Check for value in fileID for fileID-based URLS
     *
     * @return bool
     */
    private function isFileIDRequest()
    {
        return isset($this->fileID);
    }
    
    /**Check for value in uniqueID for uniqueID-based URLs
     *
     * @return bool
     */
    private function isUniqueIDRequest()
    {
        return isset($this->uniqueID);
    }
    
}
