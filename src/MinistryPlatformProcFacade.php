<?php namespace MinistryPlatformAPI;

class MinistryPlatformProcFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformProcAPI::class;
    }
}
