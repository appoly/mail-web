<?php

namespace Appoly\MailWeb\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Appoly\MailWeb\Http\Listeners\MailWebListener;

/**
 * Attaches the capture listener to every outgoing message.
 *
 * This deliberately extends the plain ServiceProvider rather than
 * Illuminate\Foundation\Support\Providers\EventServiceProvider. Every
 * subclass of the foundation event provider also attaches Laravel's
 * SendEmailVerificationNotification listener to the Registered event,
 * so extending it made host applications send two verification emails
 * for every registration.
 */
class MessageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(MessageSending::class, MailWebListener::class);
    }
}
