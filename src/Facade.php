<?php namespace MinistryPlatformAPI;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformAPI::class;
    }
}
