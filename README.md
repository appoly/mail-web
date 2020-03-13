# Mail Web

Mail Web is a Laravel package allowing you to debug emails in one place

[![StyleCi](https://github.styleci.io/repos/245465277/shield)](https://github.styleci.io/repos/245465277)

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
php artisan vendor:publish --tag=public --force
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

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
