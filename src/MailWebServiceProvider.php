<?php

namespace Appoly\MailWeb;

use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\ServiceProvider;
use Appoly\MailWeb\Providers\MessageServiceProvider;
use Appoly\MailWeb\Console\Commands\PruneMailwebMails;

class MailWebServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'MailWeb');
        $this->app->register(MessageServiceProvider::class);

        $this->commands([
            PruneMailwebMails::class,
        ]);

        // Register the main class to use with the facade
        $this->app->singleton('MailWeb', function () {
            return new MailWeb;
        });
    }

    public function boot()
    {
        $this->registerMigrations();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mailweb');
        $this->registerRoutesMacro();

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
            $router->get('/mailweb/emails', '\Appoly\MailWeb\Http\Controllers\MailWebController@fetchEmails')->name('mailweb.fetch');
            $router->get('/mailweb/emails/{id}', '\Appoly\MailWeb\Http\Controllers\MailWebController@fetchEmail')->name('mailweb.fetch-email');
            $router->post('/mailweb/emails/{id}/toggle-share', '\Appoly\MailWeb\Http\Controllers\MailWebController@toggleShare')->name('mailweb.toggle-share');
            $router->delete('/mailweb/emails/{id}', '\Appoly\MailWeb\Http\Controllers\MailWebController@delete')->name('mailweb.delete');
            $router->delete('/mailweb/emails/delete-all', '\Appoly\MailWeb\Http\Controllers\MailWebController@deleteAll')->name('mailweb.delete-all');
            $router->get('/mailweb/share/{mailwebEmail}', '\Appoly\MailWeb\Http\Controllers\MailWebController@show')->name('mailweb.share');
            $router->get('/mailweb/send-test-email', '\Appoly\MailWeb\Http\Controllers\MailWebController@sendTestEmail')->name('mailweb.send-test-email');
            $router->get('/mailweb/share/{mailwebEmail}', '\Appoly\MailWeb\Http\Controllers\MailWebController@show')->name('mailweb.share');
        });
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
