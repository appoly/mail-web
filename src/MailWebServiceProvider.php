<?php

namespace Appoly\MailWeb;

use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\ServiceProvider;

class MailWebServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! config('MailWeb.MAILWEB_ENABLED')) {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mail-web');
        $this->registerRoutesMacro();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('MailWeb.php'),
            ], 'mailweb-config');
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/mailweb'),
            ], 'mailweb-public');
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

    /**
     * Register routes macro.
     *
     * @param void
     * @return  void
     */
    protected function registerRoutesMacro()
    {
        $router = $this->app['router'];

        $router->macro('mailweb', function () use ($router) {
            $router->('/mailweb', 'Appoly\MailWeb\Http\Controllers\MailWebController@index');
            $router->('/mailweb/emails', 'Appoly\MailWeb\Http\Controllers\MailWebController@get');
        });
    }
}
