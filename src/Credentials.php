<?php namespace MinistryPlatformAPI;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

class Credentials
{

    /**
     * Instance of the cache
     * @var null
     */
    private $cache = null;


    public function __construct()
    {
        $this->cache = $this->initCache();
    }

    /**
     * Create token and save to cache
     * @param $response
     */
    public function save($response)
    {
        $token = $response['token_type'] . ' ' . $response['access_token'];

        // expires_in is in seconds so store in seconds
        $expiration = $response['expires_in'] / 60;

        // Store a value into cache for "expiration" minutes
        $this->cache->put('token', $token, $expiration);
    }

    /**
     * Return cached token, if available
     * @return mixed
     */
    public function get()
    {
        return $this->cache->has('token') ? $this->cache->get('token') : false;
    }

    /**
     * @return bool
     */
    public function forget()
    {
        return $this->cache->has('token') ? $this->cache->forget('token') : false;
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

