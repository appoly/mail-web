<p align="center">
  <img width="460" height="auto" src="https://www.appoly.co.uk/app/uploads/2020/03/MailWebInline.png"> 
</p>

Mail Web is a Laravel package allowing you to debug emails in one place

[![StyleCi](https://github.styleci.io/repos/245465277/shield)](https://github.styleci.io/repos/245465277)
[![Latest Stable Version](https://poser.pugx.org/appoly/mail-web/v/stable?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![License](https://poser.pugx.org/appoly/mail-web/license?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![Total Downloads](https://poser.pugx.org/appoly/mail-web/downloads?format=flat-square)](https://packagist.org/packages/appoly/mail-web)

## Installation

Use the package manager [composer](https://getcomposer.org/) to install Mail Web.

```bash
composer require appoly/mail-web
```

## Usage

Run the migration

```bash
php artisan migrate
```

Publish the assets to your project using

```bash
php artisan vendor:publish --tag=mailweb-public --force
```

Publish the config to your project using

```bash
php artisan vendor:publish --tag=mailweb-config --force
```

Add MailSending to your EventServiceProvider.php

```php
use Appoly\MailWeb\Http\Listeners\MailWebListener;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageSending::class => [
            MailWebListener::class
        ]
    ];

```

To use Mail Web you need to add a Gate to your route service provider. If you would like to limit the users that can access the route then use

```php
Gate::define("view-mailweb", function ($user) {
    return in_array($user->email, [
        'user@appoly.co.uk',
    ]);
});
```

Should you want to allow access to all users then change the above code to

```php
Gate::define("view-mailweb", function ($user) {
    return true;
});
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
