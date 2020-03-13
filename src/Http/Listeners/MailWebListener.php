<?php

namespace Appoly\MailWeb\Http\Listeners;

use Appoly\MailWeb\Http\Models\MailWeb;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
            // 'subject' => $event->message->getSubject(),
            // 'body' => $event->message->getBody(),
            // 'from_email' => array_key_first($event->message->getFrom()),
            // 'to_email' => array_key_first($event->message->getTo()),
            'email' => serialize($event->message)
        ]);
        $s = serialize($event->message);
        dump($s);
        $b = unserialize($s);
        dd($b->toString(), $b->getFrom());
        // dd($event->message->toString());
    }
}
