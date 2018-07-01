<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;

class MinistryPlatformTableAPI
{
    use MPoAuth;

    /**
     * parameters to be used for the Tables API calls
     *
     * @var null
     */
    protected $tableName = null;
    protected $select = '*';
    protected $filter = null;
    protected $orderby = null;
    protected $groupby = null;
    protected $having = null;
    protected $top = null;
    protected $distinct = null;
    protected $skip = 0;

    protected $recordID = null;

    // Row data for update requests
    protected $records = null;

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
     * Set the Group By clause for the GET request
     * @param $groupby
     * @return $this
     */
    public function groupBy($groupby)
    {
        $this->groupby = $groupby;

        return $this;
    }

    /**
     * Set the Having caluse for the GET request
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        $this->having = $having;

        return $this;
    }

    /**
     * Set the Top parameter for the GET Request
     * @param $top | integer
     * @return $this
     */
    public function top($top){
        $this->top = $top;

        return $this;
    }

    public function distinct($distinct)
    {
        $this->distinct = $distinct ? 'true' : 'false';

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
        return $this->getResults($endpoint);
    }

    /**
     * Get only the first returned result
     *
     * @return bool
     */
    public function first()
    {
        if ($results = $this->get()) {
            $this->reset();
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

    /**
     * Delete a record with the supplied ID
     *
     */
    public function delete($id)
    {
        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        $endpoint .= '/' . $id;


        // Set the header
        $this->buildHttpHeader();

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        try {

            $response = $client->request('DELETE', $endpoint, [
                'headers' => $this->headers,
                'curl' => $this->setGetCurlopts(),
            ]);

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
     * Request data 1000 rows at a time until all data has been retrieved
     * The JSON API returns a max of 1000 records per request.  Use
     * the $skip to move the results window 1000 records at a time.
     *
     */
    private function getResults($endpoint)
    {
        $results = [];
        $this->errorMessage = null;

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        do {
            try {
                $response = $client->request('GET', $endpoint, [
                    'headers' => $this->headers,
                    'query' => ['$select' => $this->select,
                        '$filter' => $this->filter,
                        '$orderby' => $this->orderby,
                        '$groupby' => $this->groupby,
                        '$having' => $this->having,
                        '$top' => $this->top,
                        '$skip' => $this->skip,
                        '$distinct' => $this->distinct
                    ],
                    'curl' => $this->setGetCurlopts(),
                ]);

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

            $r = json_decode($response->getBody(), true);

            // Get the number of rows returned
            $num = count($r);

            // Add this result set to the previous results
            $results = array_merge($results, $r);

            // Skip the rows we just got back if there were 1000 of them and query again
            ($num == 1000) ? $this->skip += 1000 : $this->skip = 0;

        } while ($this->skip > 0);

        $this->reset();
        return $results;
    }



    private function sendData($verb)
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
                'body' => $this->records,
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
    
        return $error ? $error : json_decode($response->getBody(), true);

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
        $auth = 'Authorization: ' . $this->credentials->get('token');
        $scope = 'Scope: ' . $this->scope;
        $this->headers = ['Accept: application/json', 'Content-type: application/json', $auth, $scope];
    }

    /**
     * Reset query parameters
     */
    private function reset()
    {
        $this->tableName = null;
        $this->select = '*';
        $this->filter = null;
        $this->orderby = null;
        $this->skip = 0;
        $this->groupby = null;
        $this->having = null;
        $this->top = null;
        $this->distinct = null;

        $this->recordID = null;

        $this->records = null;
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
     * Return the error message from the request
     *
     * @return null
     */
    public function errorMessage()
    {
        return $this->errorMessage;
    }
}