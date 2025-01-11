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
    <!-- TODO: Get a working image/gif for this section -->
    <!-- <img width="1080" height="auto" src="https://www.appoly.co.uk/app/uploads/2024/03/Screenshot-2024-03-01-at-16.38.06.png"> -->
</p>

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Setup](#setup)
  - [Limiting the number of stored emails](#limiting-the-number-of-stored-emails)
  - [Storing Attachments on a Disk](#storing-attachments-on-a-disk)
- [Migrating to v5](#migrating-to-v5)
- [Contributing](#contributing)
- [License](#license)


## Features

- **Intercept Outgoing Emails:** Catch and view your application's emails in real-time for faster debugging.
- **Tailwind-Powered UI:** Navigate with ease using a responsive, modern interface.
- **Shareable Email Previews:** Collaborate with your team effortlessly by sharing email previews.
- **Advanced Search:** Quickly locate emails with robust search functionality.

## Installation

Use the package manager [composer](https://getcomposer.org/) to install Mail Web.

```bash
composer require appoly/mail-web
```

## Usage

### Setup

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

### Laravel 11 Notes
In Laravel 11, the `AuthServiceProvider` is not included in new projects by default. Gates can be defined in the `AppServiceProvider` instead. 

In your local environment, it's advised to set your mail driver to LOG to prevent SMTP errors.

```
MAIL_MAILER=log
```

To view emails then go to

```
{url}\mailweb
```

### Limiting the number of stored emails

The number of emails stored is handled via a command that must be setup to run. You can choose how often this needs to run according to how many emails you receive. Below, we have showed it being set up to run daily.

The remaining number is customisable via the `MAILWEB_LIMIT` config variable, which you can specify in your `.env`, or the default of 30 will be used.

The recommended place to schedule commands is in `routes/console.php`:

```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;

// ... Your other commands here
Schedule::command('mailweb:prune')->daily();
```

Or on older laravel versions which have been upgraded manually, you may still be using `app/Console/Kernel.php`:

```php
    protected function schedule(Schedule $schedule)
    {
        // ... Your other commands here
        $schedule->command('mailweb:prune')->dailyAt('01:00');
    }
```

### Storing Attachments on a disk (eg. s3)

To store attachments on a disk, the config variable `MAILWEB_ATTACHMENTS.DISK` must be set to the disk name, which should exist in your app's `config/filesystems.php` file. This is set via a `.env` variable `MAILWEB_ATTACHMENTS_DISK`.

Eg. If you have a disk called `s3` setup, then adding `MAILWEB_ATTACHMENTS_DISK=s3` to your `.env` file will store attachments on the `s3` disk.

The default path is `/mailweb/attachments/...`, and this can be customised but updating the `MAILWEB_ATTACHMENTS_PATH` env variable to whatever you wish.

When mails are deleted, the attachments will be deleted as well if the disk and path have remained unchanged from when the attachment was created.

## Migrating to v5

If you previously used MailWeb you will notice a new archived table. This is because we have changed to data structure making it easier to pull out the email data we need rather than storing the whole email object. We are working on a command to migrate any stored emails over but for the time being these emails will no longer be viewable.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

### Setup

There are multiple ways to set up a local composer project, one of which is as follows:

1. Clone this repository
2. Run `composer install`
3. Note the path to the directory
4. Go to another php/Laravel project and add the following items to your composer.json:

```php
"repositories": [
    {
        "type": "path",
        "url": "../path/to/MailWeb",
        "options": {
            "symlink": true
        }
    }
],
```

5. Change the require section with `@dev` for the package:

```php
"require": {
    "appoly/mail-web": "@dev"
```

6. Run `composer update` in this project, and it should now be linked to the dev version of MailWeb

## License

[MIT](https://choosealicense.com/licenses/mit/)
