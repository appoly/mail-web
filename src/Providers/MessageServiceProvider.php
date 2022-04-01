<?php

namespace Appoly\MailWeb\Providers;

use Appoly\MailWeb\Http\Listeners\MailWebListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class MessageServiceProvider extends EventServiceProvider
{
    protected $listen = [
        MessageSending::class => [
            MailWebListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
