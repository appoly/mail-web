<?php

namespace Appoly\MailWeb\Http\Listeners;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Mail\Events\MessageSending;

class MailWebListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        MailwebEmail::create([
            'email' => serialize($event->message),
        ]);
    }
}
