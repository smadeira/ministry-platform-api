<?php namespace MinistryPlatformAPI;

use Illuminate\Support\Facades\Facade;

class MinistryPlatformTableFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformTableAPI::class;
    }
}
