<div align="center">

# MailWeb

Catch and inspect outgoing emails from your Laravel application in a web dashboard.

[![Total Downloads](https://poser.pugx.org/appoly/mail-web/downloads?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![Latest Stable Version](https://poser.pugx.org/appoly/mail-web/v/stable?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![License](https://poser.pugx.org/appoly/mail-web/license?format=flat-square)](https://packagist.org/packages/appoly/mail-web)

</div>

MailWeb intercepts emails as they're sent and stores them in your database. You get a dashboard at `/mailweb` where you can search, filter, preview, and share captured emails, including any attachments.

## Requirements

- PHP 8.1+
- Laravel 9.21, 10, 11, 12, or 13

## Installation

Install the package:

```bash
composer require appoly/mail-web
```

Run the migrations:

```bash
php artisan migrate
```

Register the routes in your `routes/web.php`:

```php
Route::mailweb();
```

That's it. Emails sent by your application will now be captured and available at `/mailweb`.

## Access Control

MailWeb uses a Laravel Gate to control who can access the dashboard. Define the `view-mailweb` gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('view-mailweb', function ($user) {
        return in_array($user->email, [
            'you@example.com',
        ]);
    });
}
```

All dashboard routes require authentication and this gate check. The only exception is the public share link (`/mailweb/share/{id}`), which is accessible to anyone if sharing is enabled on that email.

## Configuration

Publish the config file if you need to change anything:

```bash
php artisan vendor:publish --tag=mailweb-config --force
```

This creates `config/MailWeb.php`. Everything can also be set via environment variables:

| Environment Variable | Default | Description |
|---|---|---|
| `MAILWEB_ENABLED` | `true` | Enable or disable email capturing entirely |
| `MAILWEB_LIMIT` | `30` | Maximum number of emails kept in the database |
| `MAILWEB_SEND_SAMPLE_BUTTON` | `true` | Show the "Send Test Email" button in the dashboard |
| `MAILWEB_DELETE_ALL_ENABLED` | `false` | Allow bulk deletion of all emails from the dashboard |
| `MAILWEB_RETURN_APP_NAME` | Your `app.name` | App name shown in the dashboard's return link |
| `MAILWEB_RETURN_APP_URL` | `/` | URL the return link points to |
| `MAILWEB_ATTACHMENTS_DISK` | `null` | Storage disk for attachments (e.g. `s3`, `local`) |
| `MAILWEB_ATTACHMENTS_PATH` | `mailweb/attachments` | Path within the disk where attachments are stored |

## Attachments

By default, attachment metadata is stored in the database but the files themselves aren't persisted to disk. To keep the actual files, configure a storage disk:

```env
MAILWEB_ATTACHMENTS_DISK=s3
MAILWEB_ATTACHMENTS_PATH=mailweb/attachments
```

This works with any Laravel filesystem disk. Attachments are stored at `{path}/{email_id}/{attachment_id}.{extension}`. If the disk supports temporary URLs (like S3), download links will be signed and expire after 30 minutes.

## Pruning Old Emails

MailWeb stores up to `MAILWEB_LIMIT` emails. To clean up older ones automatically, schedule the prune command in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('mailweb:prune')->daily();
```

You can also run it manually with an optional count of how many emails to keep:

```bash
php artisan mailweb:prune 50
```

If no count is provided, it uses the `MAILWEB_LIMIT` config value.

## Dashboard Features

- **Search** across subject and body content
- **Filter** by unread status or whether emails have attachments
- **Preview** HTML, plain text, and raw source for each email
- **Share** individual emails via a public link with QR code
- **Attachments** can be previewed (images) or downloaded
- **Send test emails** using one of five built-in templates to check your setup
- **Delete** individual emails or all at once (if enabled)
- **Polling** for new emails in real time
- **Settings** for date format and pagination preferences (stored in your browser)

## Local Development

For local development, you probably want to set your mail driver to `log` so emails aren't actually sent anywhere:

```env
MAIL_MAILER=log
```

Emails will still be captured and shown in MailWeb regardless of which mail driver you use.

### Working on the Package

If you're contributing to MailWeb itself, symlink it into a test Laravel project:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/mail-web",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "appoly/mail-web": "@dev"
    }
}
```

Frontend assets are built with Vite:

```bash
npm install
npm run build
```

Built assets are committed to `public/vendor/mailweb/` so end users don't need Node.

## Licence

MIT. See [LICENSE](https://choosealicense.com/licenses/mit/) for details.

---

<div align="center">
Made by <a href="https://appoly.co.uk">Appoly</a>
</div>
