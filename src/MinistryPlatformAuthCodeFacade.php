<?php namespace MinistryPlatformAPI;

class MinistryPlatformAuthCodeFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return oAuthAuthorizationCode::class;
    }
}
