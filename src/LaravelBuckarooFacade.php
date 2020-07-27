<?php


namespace Raydotnl\LaravelBuckaroo;


use Illuminate\Support\Facades\Facade;

class LaravelBuckarooFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-buckaroo';
    }
}
