<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;
use MinistryPlatformAPI\MPoAuth;

class MinistryPlatformAPI
{
    use MPoAuth;

    /**
     * parameters to be used for the Tables API calls
     *
     * @var null
     */
    public $tableName = null;
    public $select = '*';
    public $filter = null;
    public $orderby = null;
    public $skip = 0;

    // Row data for update requests
    public $records = null;

    
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
    public function records(Array $records)
    {
        $this->records = json_encode($records);

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

        // Get all of the results 1000 at a time
        return  $this->getResults($endpoint);

    }

    /**
     * Request data 1000 rows at a time until all data has been retrieved
     * The JSON API returns a max of 1000 records per request.  Use 
     * the $skip to move the results window 1000 records at a time.
     * 
     */
    private function getResults($endpoint)
    {
        $results = [];

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        do {
            try {
                $response = $client->request('GET', $endpoint, [
                    'headers' => $this->headers,
                    'query' => ['$select' => $this->select,
                        '$filter' => $this->filter,
                        '$orderby' => $this->orderby,
                        '$skip' => $this->skip],
                    'curl' => $this->setGetCurlopts(),
                ]);

            } catch (GuzzleException $e) {
                print_r($e->getResponse()->getBody()->getContents());
                return false;

            } catch (GuzzleHttp\Exception\ClientException $e) {
                echo $e->getResponse()->getBody()->getContents();
                return false;
            }

            $r = json_decode($response->getBody(), true);
            
            // Get the number of rows returned
            $num = count($r);

            // Add this result set to the previous results
            $results = array_merge($results, $r);

            // Skip the rows we just got back
            $this->skip += 1000;

        } while ( $num > 0 );

        return $results;
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
        return $this->sendData('PUT');
        
    }

    // POST a new record to the database
    public function post()
    {
        return $this->sendData('POST');
    }


    private function sendData($verb) {

        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $this->buildHttpHeader();

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        echo $this->records . "\n";

        try {

            $response = $client->request($verb, $endpoint, [
                'headers' => $this->headers,
                'query' => ['$select' => $this->select],
                'body' => $this->records,
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
     * Construct the API Endpoint for the request
     *
     * @return string
     */
    private function buildEndpoint()
    {
        return $this->apiEndpoint . '/tables/' . $this->tableName . '/';
    }
   
    private function buildHttpHeader()
    {
        // Set the header
        $auth = 'Authorization: ' . $this->token_type . ' ' . $this->access_token;
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];

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
}