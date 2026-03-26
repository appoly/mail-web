<?php

namespace Appoly\MailWeb\Providers;

use Illuminate\Mail\Events\MessageSending;
use Appoly\MailWeb\Http\Listeners\MailWebListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class MessageServiceProvider extends EventServiceProvider
{
    protected $listen = [
        MessageSending::class => [
            MailWebListener::class,
        ],
    ];
}
