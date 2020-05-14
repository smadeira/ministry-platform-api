<?php namespace MinistryPlatformAPI;

use Illuminate\Support\Facades\Facade;

class MinistryPlatformProcFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformProcAPI::class;
    }
}
