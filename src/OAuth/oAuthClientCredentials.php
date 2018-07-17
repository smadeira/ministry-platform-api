<?php namespace MinistryPlatformAPI\OAuth;

use GuzzleHttp\Client;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;


class oAuthClientCredentials extends oAuthBase
{

    /**
     * oAuth Token Request Results
     *
     * @var null
     */
    public $credentials = null;

    /**
     * Cache to store the credentials
     * @var null
     */
    private $cache = null;


    public function __construct()
    {
        // Get endpoint
        $this->getCongfigParameters();

        // Initialize the cache
        $this->cache = $this->initCache();

        // Get credentials if they are in the cache
        if ($this->cache->has('creds') ){
            $creds = $this->cache->get('creds');
            $this->credentials = unserialize($creds);
        }
    }

    /**
     * Performs discovery request and gets the token
     * @return $this|bool
     */
    public function clientCredentials()
    {
        if (! $this->credentials) {
            // $this->credentials = new ClientCredentials;
            $this->credentials = new Credentials('client_credentials');

            // Get API endpoints
            $this->endpointDiscovery();

            // Acquire the tokens and expiration and save
            $body = $this->acquireToken();
            $this->credentials->set($body);

            // Add credentials to the cache
            $creds = serialize($this->credentials);
            $expiration = $this->credentials->getExpiration();
            $this->cache->put('creds', $creds, $expiration );
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
        if ($this->credentials) {
            $this->credentials->forget();
            return true;
        }

        return false;
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
            $body = $this->parseTokenResponse($response);

        } catch (\GuzzleException $e) {
            return false;
        }

        return $body;
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
        return json_decode($response->getBody(), true);
    }


    /**
     * Create a file-based cache repository
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function initCache()
    {
        // Create a new Container object, needed by the cache manager.
        $container = new Container;

        // The CacheManager creates the cache "repository" based on config values
        $container['config'] = [
            'cache.default' => 'file',
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/cache'
            ]
        ];

        // To use the file cache driver we need an instance of Illuminate's Filesystem, also stored in the container
        $container['files'] = new Filesystem;
        // Create the CacheManager
        $cacheManager = new CacheManager($container);

        // Get the default cache driver (file in this case)
        return $cacheManager->store();

    }

}