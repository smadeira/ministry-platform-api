<?php namespace MinistryPlatformAPI;

class MinistryPlatformTableFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformTableAPI::class;
    }
}
