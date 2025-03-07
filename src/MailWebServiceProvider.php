<?php

namespace Appoly\MailWeb;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Appoly\MailWeb\Providers\MessageServiceProvider;
use Appoly\MailWeb\Console\Commands\PruneMailwebMails;
use Appoly\MailWeb\Notifications\MailwebSampleNotification;

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
            $router->get('/mailweb/send-test-email', function (Request $request) {
                // Temporarily set mail driver to log
                $originalMailDriver = Config::get('mail.default');
                Config::set('mail.default', 'log');

                // Get template ID from request or use random template
                $templateId = $request->input('template_id');

                try {
                    // Send notification to example@appoly.co.uk
                    Notification::route('mail', 'example@appoly.co.uk')
                        ->notify(new MailwebSampleNotification($templateId));

                    // Reset mail driver
                    Config::set('mail.default', $originalMailDriver);

                    return new JsonResponse(['success' => true, 'message' => 'Test email sent to logs']);
                } catch (\Exception $e) {
                    // Reset mail driver in case of error
                    Config::set('mail.default', $originalMailDriver);

                    return new JsonResponse(
                        ['success' => false, 'message' => 'Failed to send test email', 'error' => $e->getMessage()],
                        500
                    );
                }
            })->name('mailweb.send-test-email');
            $router->get('/mailweb/{mailwebEmail}', '\Appoly\MailWeb\Http\Controllers\MailWebController@show')->name('mailweb.show');

            // Add route for sending test email - using GET to avoid CSRF issues

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
