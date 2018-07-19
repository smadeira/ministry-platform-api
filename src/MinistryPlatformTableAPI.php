<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;


class MinistryPlatformTableAPI extends MinistryPlatformBaseAPI
{
    /**
     * parameters to be used for the Tables API calls
     *
     * @var null
     */
    protected $tableName = null;

    /**
     * Fields to be returned in a GET.  API will return all fields in
     * POST and PUT operations unless limited by a field list specified here.
     * @var string
     */
    protected $select = '*';

    /**
     * WHERE clause in "MP-SQL" format
     * @var null
     */
    protected $filter = null;

    /**
     * Sort order of results
     * @var null
     */
    protected $orderby = null;

    /**
     * Grouping for aggregation functions
     * @var null
     */
    protected $groupby = null;

    /**
     * SQL Having clause
     * @var null
     */
    protected $having = null;

    /**
     * Top clause to limit rows returned
     * @var null
     */
    protected $top = null;

    /**
     * SQL Distinct
     * @var null
     */
    protected $distinct = null;

    /**
     * Pagination control.  Skip a certain number of rows.
     * @var int
     */
    protected $skip = 0;

    /**
     * For single record GETs, specify the PK of the record of interest
     * @var null
     */
    protected $recordID = null;

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

    /**
     * Set the recordID for
     * @param $recordID
     * @return $this
     */
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
     * Set the Having clause for the GET request
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

    /**
     * Set the Distinct attribute for the query
     * @param $distinct
     * @return $this
     */
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
        $this->postFields = json_encode($records);

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
     * Get a single record from a defined table
     */
    public function getSingle()
    {
        // Set the endpoint
        $endpoint = $this->buildEndpoint();

        // Set the header
        $this->buildHttpHeader();

        // Send the request
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->request('GET', $endpoint, [
                'headers' => $this->headers,
                'query' => ['$select' => $this->select,
                    'id' => $this->recordID,
                ],
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

        return json_decode($response->getBody(), true);
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
        $parameters = [
            'headers' => $this->buildHttpHeader(),
            'query' => ['$select' => $this->select],
            'body' => $this->postFields,
            'curl' => $this->setPostCurlopts(),
        ];

        $results = $this->sendData('PUT', $parameters);
        $this->reset();
        return $results;
    }

    // POST a new record to the database
    public function post()
    {
        $parameters = [
            'headers' => $this->buildHttpHeader(),
            'query' => ['$select' => $this->select],
            'body' => $this->postFields,
            'curl' => $this->setPostCurlopts(),
        ];

        $results = $this->sendData('POST', $parameters);
        $this->reset();
        return $results;
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
            } catch (\GuzzleHttp\Exception\RequestException $e) {
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


    /**
     * Construct the API Endpoint for the request
     *
     * @return string
     */
    protected function buildEndpoint()
    {
        $endpoint = $this->authorization->apiEndpoint . '/tables/' . $this->tableName . '/';

        // If there is a specific record ID, append that to the endpoint
        if ($this->recordID) { $endpoint .= $this->recordID; }

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

        $this->postFields = null;
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