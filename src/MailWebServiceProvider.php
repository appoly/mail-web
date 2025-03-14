<?php

namespace Appoly\MailWeb;

use Illuminate\Support\Js;
use Illuminate\Support\HtmlString;
use Appoly\MailWeb\Facades\MailWeb;
use Illuminate\Support\ServiceProvider;
use Appoly\MailWeb\Providers\MessageServiceProvider;
use Appoly\MailWeb\Console\Commands\PruneMailwebMails;

class MailWebServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'MailWeb');
        $this->app->register(MessageServiceProvider::class);

        $this->commands([
            PruneMailwebMails::class,
        ]);

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
        }
    }

    /**
     * Get the CSS or JS asset for the MailWeb dashboard.
     *
     * @param  string  $assetType  Either 'css' or 'js'
     */
    private static function getAsset(string $assetType): \Illuminate\Contracts\Support\Htmlable
    {
        $manifestPath = __DIR__ . '/../public/vendor/mailweb/.vite/manifest.json';

        if (! file_exists($manifestPath)) {
            return new HtmlString('');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (! $manifest) {
            return new HtmlString('');
        }

        $assetKey = $assetType === 'css' ? 'style.css' : 'resources/js/app.js';

        if (! isset($manifest[$assetKey]['file'])) {
            return new HtmlString('');
        }

        $assetPath = __DIR__ . '/../public/vendor/mailweb/' . $manifest[$assetKey]['file'];

        if (! file_exists($assetPath)) {
            return new HtmlString('');
        }

        $content = file_get_contents($assetPath);

        if ($assetType === 'css') {
            return new HtmlString("<style>{$content}</style>");
        } else {
            $config = Js::from([
                'deleteAllEnabled' => config('MailWeb.MAILWEB_DELETE_ALL_ENABLED', false),
                'sendSampleButton' => config('MailWeb.MAILWEB_SEND_SAMPLE_BUTTON', true),
                'return' => [
                    'appName' => config('MailWeb.MAILWEB_RETURN.APP_NAME') ?? config('app.name') ?? 'App',
                    'appUrl' => config('MailWeb.MAILWEB_RETURN.APP_URL') ?? '/',
                ],
            ]);

            return new HtmlString(<<<HTML
            <script type="module">
                window.mailwebConfig = {$config};
                {$content}
            </script>
            HTML);
        }
    }

    /**
     * Get the CSS for the MailWeb dashboard.
     */
    public static function css(): \Illuminate\Contracts\Support\Htmlable
    {
        return self::getAsset('css');
    }

    /**
     * Get the JS for the MailWeb dashboard.
     */
    public static function js(): \Illuminate\Contracts\Support\Htmlable
    {
        return self::getAsset('js');
    }

    /**
     * Register routes macro.
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
        });
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        }
    }
}
