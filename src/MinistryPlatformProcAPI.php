<?php namespace MinistryPlatformAPI;


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
        // Set the header
        $this->buildHttpHeader();

        $parameters = [
            'headers' => $this->headers,
            'body' => $this->postFields,
            'curl' => $this->setPostCurlopts(),
        ];

        // Get all of the results 1000 at a time
        return  $this->sendData('POST', $parameters);
    }
  
    /**
     * Construct the API Endpoint for the request
     *
     * @return string
     */
    protected function buildEndpoint()
    {
        return $this->authorization->apiEndpoint . '/procs/' . $this->procName . '/';
    }
   
    protected function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->authorization->credentials->getAccessToken();
        $scope = 'Scope: ' . $this->authorization->scope;
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