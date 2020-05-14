<?php namespace MinistryPlatformAPI;

use Illuminate\Support\Facades\Facade;

class MinistryPlatformFileFacade extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return MinistryPlatformFileAPI::class;
    }
}
