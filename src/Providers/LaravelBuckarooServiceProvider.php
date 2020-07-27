<?php


namespace Raydotnl\LaravelBuckaroo\Providers;


use Illuminate\Support\ServiceProvider;
use Raydotnl\LaravelBuckaroo\LaravelBuckaroo;

class LaravelBuckarooServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-buckaroo.php' => config_path('laravel-buckaroo.php')
        ]);
    }

    public function register()
    {
        $this->app->alias(LaravelBuckaroo::class, 'laravel-buckaroo');
    }
}
