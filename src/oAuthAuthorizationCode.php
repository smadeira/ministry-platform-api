<?php namespace MinistryPlatformAPI;

use GuzzleHttp\Client;

class oAuthAuthorizationCode extends oAuthBase
{

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Build the authorization code request URL
     * @return bool
     */
    public function authorizationCodeUrl()
    {
        // Get the Discovery URI
        if (!$this->endpointDiscovery()) return false;

        $url = $this->authorization_endpoint;
        $url .= '?response_type=code';
        $url .= '&client_id=' . $this->mpClientId;
        $url .= '&scope=' . $this->scope . ' openid';
        $url .= '&redirect_uri=' . $this->mpRedirectURL;

        return $url;
    }

    /**
     * Use the Authorization Code to get an Access Token
     *
     * @param $code
     * @return bool
     */
    public function acquireAccessToken($code)
    {
        // Request the token
        $client = new Client(); //GuzzleHttp\Client

        try {
            $response = $client->post($this->token_endpoint, [
                'form_params' => $this->getTokenFields($code),
                'curl' => $this->setOauthCurlopts(),
            ]);

            // Get the token and type from the response
            // $this->parseTokenResponse($response);
            $body = json_decode($response->getBody(), true);
            dd($body);

        } catch (GuzzleException $e) {

            return false;
        }
        return true;
    }


    /**
     * Field list for Access Token Request
     *
     * @return array|null
     */
    private function getTokenFields($code)
    {
        $this->oAuthFields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->mpRedirectURL,
            'client_id' => $this->mpClientId,
            'client_secret' => $this->mpClientSecret,
        ];

        $this->fieldCount = count($this->oAuthFields);

        return $this->oAuthFields;
    }


    /**
     * Erase the credentials
     *
     * @return bool
     */
    public function clear()
    {
        if (!$this->credentials) {
            $this->credentials = new Credentials;
        }

        $this->credentials->forget();

        return true;
    }


    /**
     * Get a new Access token
     *
     * @return bool
     */
    private function getToken()
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

        } catch (GuzzleException $e) {

            return false;
        }

        return true;
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