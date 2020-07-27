<?php


namespace Raydotnl\LaravelBuckaroo\Facades;

use Illuminate\Support\Facades\Facade;

class BuckarooTransaction extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Raydotnl\LaravelBuckaroo\BuckarooTransaction::class;
    }
}
