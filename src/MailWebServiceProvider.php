<?php

namespace Appoly\MailWeb;

use Illuminate\Support\ServiceProvider;

class MailWebServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mail-web');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('MailWeb.php'),
            ], 'config');
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/mailweb'),
            ], 'public');
        }
    }

    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'MailWeb');

        // Register the main class to use with the facade
        $this->app->singleton('MailWeb', function () {
            return new MailWeb;
        });
    }
}
