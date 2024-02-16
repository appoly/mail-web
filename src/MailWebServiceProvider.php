<?php

namespace Appoly\MailWeb;

use Livewire\Livewire;
use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\ServiceProvider;
use Appoly\MailWeb\Livewire\EmailListView;
use Appoly\MailWeb\Providers\MessageServiceProvider;

class MailWebServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'MailWeb');
        $this->app->register(MessageServiceProvider::class);

        // Register the main class to use with the facade
        $this->app->singleton('MailWeb', function () {
            return new MailWeb;
        });
    }

    public function boot()
    {
        if (! config('MailWeb.MAILWEB_ENABLED')) {
            return;
        }

        $this->registerMigrations();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mailweb');
        $this->registerRoutesMacro();
        $this->registerLivewireComponents();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('MailWeb.php'),
            ], 'mailweb-config');
            $this->publishes([
                __DIR__ . '/../public/vendor/mailweb' => public_path('vendor/mailweb'),
            ], 'mailweb-public');
        }
    }

    /**
     * Register routes macro.
     *
     * @param void
     * @return void
     */
    protected function registerRoutesMacro()
    {
        $router = $this->app['router'];

        $router->macro('mailweb', function () use ($router) {
            $router->get('/mailweb', '\Appoly\MailWeb\Http\Controllers\MailWebController@index')->name('mailweb.index');
            // $router->get('/mailweb/emails', '\Appoly\MailWeb\Http\Controllers\MailWebController@get');
            // $router->delete('/mailweb/emails/{mailwebEmail}', '\Appoly\MailWeb\Http\Controllers\MailWebController@delete');
        });
    }

    /**
     * Register livewire components.
     *
     * @param void
     * @return void
     */
    protected function registerLivewireComponents()
    {
        \Livewire\Livewire::component('mailweb::email-list-view', EmailListView::class);
    }

    /**
     * Register migrations.
     *
     * @param void
     * @return void
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        }
    }
}
