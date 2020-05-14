<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;

class MinistryPlatformFileAPI extends MinistryPlatformBaseAPI
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
     * Description of the image being uploaded
     *
     * @var null
     */
    protected $description = null;
    
    /**
     * Longest dimension to be used for resizing the image. Valid values
     * seem to be 150, 300, 600 and 800 pixels (based on the attach file dialog
     *
     * @var null
     */
    protected $longestDimension = null;
    
    /**
     * Full path and filename of file to be uploaded
     *
     * @var null
     */
    protected $file = null;
    
    /**
     * UserId of the person uploading the file. Not sure what
     * this is used for.
     *
     * @var null
     */
    protected $userID = null;
    
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
     * Headers for HTTP Request
     *
     * @var null
     */
    protected $headers = null;
    
    /**
     * Fields to return on put or post, I think
     *
     * @var string
     */
    protected $select = "*";
    
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
     * Set the file description field
     *
     * @param $description
     *
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Set the longest dimension for image resize purposes
     *
     * @param $longestDimension
     *
     * @return $this
     */
    public function longestDimension($longestDimension)
    {
        $this->longestDimension = $longestDimension;
        
        return $this;
    }
    
    /**
     * Set the full path and filename to file that will be uploaded
     *
     * @param $file
     *
     * @return $this
     */
    public function file($file)
    {
        $this->file = $file;
        
        return $this;
    }
    
    /**
     * Set the ID of the user that uploaded the file (I think.)
     *
     * @param $userID
     *
     * @return $this
     */
    public function userID($userID)
    {
        $this->userID = $userID;
        
        return $this;
    }
    
    /**
     * Set the recordID for GET/POST request
     *
     * @param $recordID
     *
     * @return $this
     */
    public function fileID($fileID)
    {
        $this->fileID = $fileID;
        
        return $this;
    }
    
    /**
     * Set the recordID for GET/POST request
     *
     * @param $recordID
     *
     * @return $this
     */
    public function uniqueID($uniqueID)
    {
        $this->uniqueID = $uniqueID;
        
        return $this;
    }
    
    /**
     * Set the default image flag for GET requests
     * defaults to true if no parameter is supplied
     *
     * @param $default
     *
     * @return $this
     */
    public function default($default = true)
    {
        $this->default = $default;
        
        return $this;
    }
    
    /**
     * Set the metadata flag for the API Request
     *
     * @param bool $metadata
     */
    public function metadata($metadata = true)
    {
        $this->metadata = $metadata;
        
        return $this;
    }
    
    /**
     * Set the select field list
     *
     * @param $select
     *
     * @return $this
     */
    public function select($select)
    {
        $this->select = $select;
        
        return $this;
    }
    
    /**
     * Get requested files using pre-defined attributes
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
     * Execute the Guzzle GET Request and return the results
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
        
        return $this->formatResponse($response);
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
            $endpoint .= $this->default ? '?$default=true' : '';
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
        // echo $endpoint . "\n\n";
        return $endpoint;
    }
    
    /**
     * Build headers for GET request.  Two options:
     *  1. headers to retrieve file metadata
     *  2. headers to retrieve the actual file
     *
     * @return bool
     */
    protected function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->authorization->credentials->getAccessToken();
        $scope = 'Scope: ' . $this->authorization->scope;
        
        if ( $this->metadata ) {
            $accept = 'Accept: application/json';
        } else {
            $accept = 'Accept: image/jpeg';
        }
        
        $this->headers = [$accept, 'Content-type: application/json', $auth];
        
        return true;
    }
    
    /**
     * Get requested files using pre-defined attributes
     *
     * @return mixed
     */
    public function post()
    {
        // Set the header
        $this->buildPostHttpHeader();
        
        $parameters = [
                'multipart' => $this->buildPostParameters(),
                'headers' => $this->headers,
        ];
        
        return $this->sendData('POST', $parameters);
    }
    
    /**
     * Execute a PUT request to update existing records
     *
     * @return bool|mixed
     */
    public function put()
    {
        // Set the header
        $this->buildPostHttpHeader();
        
        $parameters = [
                'headers' => $this->headers,
                'query' => ['$select' => $this->select],
                'multipart' => $this->buildPostParameters(),
        ];
        
        $results = $this->sendData('PUT', $parameters);
        $this->reset();
        return $results;
    }
    
    public function delete($fileID)
    {
        // Set the header
        $this->buildPostHttpHeader();
        
        // Generate the endpoint
        $endpoint = $this->authorization->apiEndpoint . '/files/' . $fileID;
        
        // Send the request
        $client = new Client(); //GuzzleHttp\Client
        
        try {
            $response = $client->request('DELETE', $endpoint, [
                    'headers' => $this->headers,
                // 'curl' => $this->setPostCurlopts(),
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
        
        return $this->formatResponse($response);
    }
    
    /**
     * Headers for uploading a file
     *
     * @return array|null
     */
    protected function buildPostHttpHeader()
    {
        // Set the header
        $this->headers = [
                'Accept' => 'application/json',
                'Authorization' => $this->authorization->credentials->getAccessToken(),
        ];
        
        return $this->headers;
    }
    
    /**
     * Assemble "form parameters" for uploading the image
     *
     * @return array
     */
    private function buildPostParameters()
    {
        $parameters = [];
        
        if ( $this->file ) $parameters[] = ['name' => 'FileName', 'contents' => fopen($this->file, 'r')];
        if ( $this->description ) $parameters[] = ['name' => 'Description', 'contents' => $this->description];
        if ( $this->default ) $parameters[] = ['name' => '$default', 'contents' => $this->default];
        if ( $this->longestDimension ) $parameters[] = ['name' => 'longestDimension', 'contents' => $this->longestDimension];
        if ( $this->userID ) $parameters[] = ['name' => '$userId', 'contents' => $this->userID];
        
        return $parameters;
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
     * Return an appropriate GET response with either metadata or an actual file
     *
     * @param $response
     *
     * @return mixed
     */
    private function formatResponse($response)
    {
        if ( $this->metadata ) {
            // Asking for metadata - return array
            return json_decode($response->getBody(), true);
        } else {
            // Asking for the file - return the stream resource
            return $response->getBody()->getContents();
        }
    }
    
    /**
     * Reset all parameters after an API call
     */
    private function reset()
    {
        $this->tableName = null;
        $this->recordID = null;
        $this->default = null;
        $this->description = null;
        $this->longestDimension = null;
        $this->file = null;
        $this->userID = null;
        $this->fileID = null;
        $this->uniqueID = null;
        $this->metadata = false;
        $this->headers = null;
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
    
    /**
     * Dump the contents of the object for debugging purposes
     *
     * @return $this
     */
    public function dump()
    {
        print_r($this);
        
        return $this;
    }
    
}
