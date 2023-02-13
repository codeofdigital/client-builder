<?php

namespace CodeOfDigital\ClientBuilder;

use CodeOfDigital\ClientBuilder\Commands\ClientBuilderCommand;
use Illuminate\Support\ServiceProvider;

class ClientBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishAssets();
        $this->mergeConfigFrom(__DIR__.'/../config/builder.php', 'builder');
    }

    protected function publishAssets()
    {
        if ($this->app->runningInConsole())
            $this->publishes([__DIR__.'/../config/builder.php' => base_path('config/builder.php')]);
    }

    public function register()
    {
        if ($this->app->runningInConsole())
            $this->commands([ClientBuilderCommand::class]);
    }
}