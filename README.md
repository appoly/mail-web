<h1 align="center"> MailWeb</h1>

<p align="center">
    MailWeb is a Laravel package designed to help developers with emails. With MailWeb you can effortlessly catch and view your application's outgoing emails. This allows developers to quickly see, debug and share emails.
</p>
<p align="center">
    <a href="https://packagist.org/packages/appoly/mail-web"><img src="https://poser.pugx.org/appoly/mail-web/downloads?format=flat-square" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/appoly/mail-web"><img src="https://poser.pugx.org/appoly/mail-web/v/stable?format=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/appoly/mail-web"><img src="https://poser.pugx.org/appoly/mail-web/license?format=flat-square" alt="License"></a>
</p>

<p align="center">
    <img width="1080" height="auto" src="https://www.appoly.co.uk/app/uploads/2024/03/Screenshot-2024-03-01-at-16.38.06.png">
</p>

## Features
- Outgoing Email Capture: Intercept outgoing emails from your Laravel application seamlessly.
- Tailwind UI: Enjoy a sleek and responsive user interface crafted with Tailwind CSS.
- Email Viewing: Easily view captured emails within the Mail Web dashboard.
- Shareable Links: Generate shareable links for email previews, facilitating collaboration and debugging.
- Search Functionality: Quickly search through your emails to find the information you need.


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

For ease, you can publish the assets by adding the following to your composer.json
```json
"post-update-cmd": [
    "@php artisan vendor:publish --tag=mailweb-public --force"
]
```

Register the routes using the mailweb macro

```php
Route::mailweb();
```

To use Mail Web you need to add a Gate to your AuthServiceProvider. If you would like to limit the users that can access the route then use

```php
public function boot()
{
  Gate::define("view-mailweb", function ($user) {
      return in_array($user->email, [
          'user@appoly.co.uk',
      ]);
  });
}
```

Should you want to allow access to all users then change the above code to

```php
Gate::define("view-mailweb", function ($user) {
    return true;
});
```

Although it can be dangerous, should you want to allow access to anyone (regardless of authentication) then change the above code to

```php
Gate::define("view-mailweb", function ($user = null) {
    return true;
});
```

In your local environment, it's advised to set your mail driver to LOG to prevent SMTP errors.

```
MAIL_MAILER=log
```

To view emails then go to

```
{url}\mailweb
```

## Migrating to v5
If you previously used MailWeb you will notice a new archived table. This is because we have changed to data structure making it easier to pull out the email data we need rather than storing the whole email object. We are working on a command to migrate any stored emails over but for the time being these emails will no longer be viewable.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
