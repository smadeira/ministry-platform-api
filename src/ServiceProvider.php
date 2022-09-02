<?php namespace MinistryPlatformAPI;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {         
        $this->publishes([
            __DIR__.'/config/mp-api-wrapper.php' => config_path('mp-api-wrapper.php'),
        ]);
    }
    
}
