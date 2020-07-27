<?php


namespace Raydotnl\LaravelBuckaroo\Providers;

use Illuminate\Support\ServiceProvider;
use Raydotnl\LaravelBuckaroo\Buckaroo;
use Raydotnl\LaravelBuckaroo\BuckarooTransaction;

class BuckarooServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/buckaroo.php' => config_path('buckaroo.php'),
        ]);
    }

    public function register()
    {
        $this->app->alias(Buckaroo::class, 'buckaroo');
        $this->app->singleton(BuckarooTransaction::class,
            function () {
                return new BuckarooTransaction();
            }
        );
    }
}
