<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;


class oAuthClientCredentials extends oAuthBase
{

    /**
     * oAuth Token Request Results
     *
     * @var null
     */
    public $credentials = null;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Performs discovery request and gets the token
     * @return $this|bool
     */
    public function clientCredentials()
    {
        if (! $this->credentials) {
            $this->credentials = new ClientCredentials;
        }

        if (! $token = $this->credentials->get()){

            // Get the Discovery URI
            if (!$this->endpointDiscovery()) return false;

            // Request a token
            if (!$this->acquireToken()) return false;
        }

        return $this;
    }

    /**
     * Erase the credentials
     *
     * @return bool
     */
    public function clear()
    {
        if (! $this->credentials) {
            $this->credentials = new ClientCredentials;
        }

        $this->credentials->forget();

        return true;
    }

    /**
     * Get a new Access token
     *
     * @return bool
     */
    private function acquireToken()
    {
        // Request the token
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->post($this->token_endpoint, [
                'form_params' => $this->getOauthFields(),
                'curl' => $this->setOauthCurlopts(),
            ]);

            // Get the token and type from the response
            $this->parseTokenResponse($response);

        } catch (\GuzzleException $e) {
            return false;
        }

        return true;
    }

    /**
     * Field list for oAuth authentication
     *
     * @return array|null
     */
    private function getOauthFields()
    {
        $this->oAuthFields = [
            'grant_type' => 'client_credentials',
            'scope' => $this->scope,
            'client_secret' => $this->mpClientSecret,
            'client_id' => $this->mpClientId,
        ];

        $this->fieldCount = count($this->oAuthFields);

        return $this->oAuthFields;
    }

    /**
     * Get tokens from authentication attempt
     *
     * @param $response
     */
    private function parseTokenResponse($response)
    {
        $body = json_decode($response->getBody(), true);

        $this->credentials->save($body);
    }

}