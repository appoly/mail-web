<?php

namespace Appoly\MailWeb;

use Livewire\Livewire;
use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\Facades\Blade;
use Appoly\MailWeb\Livewire\EmailView;
use Illuminate\Support\ServiceProvider;
use Appoly\MailWeb\Livewire\EmailListView;
use Appoly\MailWeb\Livewire\SampleEmailAlert;
use Appoly\MailWeb\Livewire\DownloadAttachment;
use Appoly\MailWeb\View\Components\Forms\Input;
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
        $this->registerLivewireComponents();
        $this->registerBladeComponents();

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
            $router->get('/mailweb/{mailwebEmail}', '\Appoly\MailWeb\Http\Controllers\MailWebController@show')->name('mailweb.show');
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
        Livewire::component('mailweb::email-list-view', EmailListView::class);
        Livewire::component('mailweb::email-view', EmailView::class);
        Livewire::component('mailweb::download-attachment', DownloadAttachment::class);
        Livewire::component('mailweb::sample-email-alert', SampleEmailAlert::class);
    }

    /**
     * Register blade components.
     *
     * @param void
     * @return void
     */
    protected function registerBladeComponents()
    {
        Blade::component('mailweb::forms.input', Input::class);
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
