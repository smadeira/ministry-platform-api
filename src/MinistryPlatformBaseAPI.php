<?php namespace MinistryPlatformAPI;

use MinistryPlatformAPI\OAuth\oAuthClientCredentials;

abstract class MinistryPlatformBaseAPI
{
    /**
     * OAuth access token and other credentials
     * @var null
     */
    protected $authorization = null;

    /**
     * For POST and PUT, this is the data to be input to the database.
     * @var null
     */
    protected $postFields = null;

    /**
     * Error message from Guzzle requests
     *
     * @var null
     */
    protected $errorMessage = null;

    /**
     * The API endopoint
     * @var null
     */
    protected $apiEndpoint = null;

    /**
     * HTTP Headers for the API requests
     * @var
     */
    protected $headers;


    /**
     * Authenticate to the API and get a token
     *
     * @param string $grantType
     */
    public function authenticate($grantType = 'client_credentials')
    {
        $cc = new oAuthClientCredentials;
        $this->authorization = $cc->clientCredentials();

        return $this;
    }

    /**
     * Execute a PUT or POST request
     *
     * @param $verb
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendData($verb)
    {
        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $this->buildHttpHeader();

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        $error = true;
        try {

            $response = $client->request($verb, $endpoint, [
                'headers' => $this->headers,
                'query' => ['$select' => $this->select],
                'body' => $this->postFields,
                'curl' => $this->setPutCurlopts(),
            ]);

            $error = false;

        } catch (\GuzzleException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->errorMessage = $e->getResponse()->getBody()->getContents();

        } catch (Exception $e) {
            $this->errorMessage = 'Unknown Exception in Guzzle request';

        } finally {
            $this->reset();
        }

        return $error ? (! $error) : json_decode($response->getBody(), true);

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

    /**
     * Set the cUrl Options for a PUT request
     *
     * @return array
     */
    protected function setPostCurlopts()
    {
        $curlopts = [
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $this->postFields,
            CURLOPT_HEADER => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $curlopts;
    }


    abstract protected function buildEndpoint();
    abstract protected function buildHttpHeader();
}
