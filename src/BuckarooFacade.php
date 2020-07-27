<?php


namespace Raydotnl\LaravelBuckaroo;

use Illuminate\Support\Facades\Facade;

class BuckarooFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-buckaroo';
    }
}
