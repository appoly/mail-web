<div align="center">

# MailWeb

**A powerful Laravel email debugging and testing tool**

[![Total Downloads](https://poser.pugx.org/appoly/mail-web/downloads?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![Latest Stable Version](https://poser.pugx.org/appoly/mail-web/v/stable?format=flat-square)](https://packagist.org/packages/appoly/mail-web)
[![License](https://poser.pugx.org/appoly/mail-web/license?format=flat-square)](https://packagist.org/packages/appoly/mail-web)

</div>

## 🚀 Overview

MailWeb is a robust Laravel package that revolutionizes email development and debugging. It seamlessly captures and displays your application's outgoing emails in real-time, making email testing and sharing effortless.

## ✨ Features

- 📧 **Real-time Email Interception**: Catch and inspect outgoing emails instantly
- 🎨 **Modern UI**: Beautiful, responsive interface for easy navigation
- 🔍 **Powerful Search**: Quickly find emails with advanced search capabilities
- 🔄 **Email Sharing**: Share email previews with your team effortlessly
- 📎 **Attachment Support**: Handle email attachments with flexible storage options
- 🛡️ **Secure Access Control**: Granular control over who can access the dashboard
- 📱 **Mobile Responsive**: Optimized interface for both desktop and mobile devices

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 9.21|10.0|11.0|12.0

## 🔧 Installation

1. Install via Composer:
```bash
composer require appoly/mail-web
```

2. Run migrations:
```bash
php artisan migrate
```

3. Publish config (if needed):
```bash
php artisan vendor:publish --tag=mailweb-config --force
```

## ⚙️ Configuration

### 1. Route Registration

Add to your routes file:
```php
Route::mailweb();
```

### 2. Access Control

Add to your `AppServiceProvider` (Laravel 11+) or `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('view-mailweb', function ($user) {
        return in_array($user->email, [
            'admin@example.com',
            // Add authorized emails
        ]);
    });
}
```

### 3. Local Development

For local development, set in your `.env`:
```env
MAIL_MAILER=log
```

## 📝 Usage

1. Access the dashboard at:
```
{your-app-url}/mailweb
```

2. Configure email storage limit in `.env`:
```env
MAILWEB_LIMIT=30  # Default value
```

3. Set up email pruning (recommended in `routes/console.php`):
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('mailweb:prune')->daily();
```

## 💾 Attachment Storage

Configure attachment storage in your `.env`:
```env
MAILWEB_ATTACHMENTS_DISK=s3  # Or any configured disk
MAILWEB_ATTACHMENTS_PATH=/custom/path  # Optional, defaults to /mailweb/attachments
```



## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

### Local Development Setup

1. Clone the repository
2. Install dependencies:
```bash
composer install
```

3. Link to your test project - add to your test project's `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/MailWeb",
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

## 📄 License

This project is licensed under the [MIT License](https://choosealicense.com/licenses/mit/).

---

<div align="center">
Made by <a href="https://appoly.co.uk">Appoly</a>
</div>
