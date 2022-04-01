<?php

namespace Appoly\MailWeb;

use Appoly\MailWeb\Providers\MessageServiceProvider;
use Illuminate\Support\ServiceProvider;

class MailWebServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!config('MailWeb.MAILWEB_ENABLED')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mail-web');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('MailWeb.php')
            ], 'mailweb-config');
            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/mailweb')
            ], 'mailweb-public');
        }
    }

    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'MailWeb');
        $this->app->register(MessageServiceProvider::class);

        // Register the main class to use with the facade
        $this->app->singleton('MailWeb', function () {
            return new MailWeb();
        });
    }
}
