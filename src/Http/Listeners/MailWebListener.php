<?php

namespace Appoly\MailWeb\Http\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Appoly\MailWeb\Http\Models\MailwebEmail;

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
     * Handle and store the mailweb email.
     */
    public function handle(MessageSending $event): void
    {
        if (! config('MailWeb.MAILWEB_ENABLED')) {
            return;
        }

        MailwebEmail::create([
            'from' => $this->getAddresses($event->message->getFrom()),
            'to' => $this->getAddresses($event->message->getTo()),
            'cc' => $this->getAddresses($event->message->getCc()),
            'bcc' => $this->getAddresses($event->message->getBcc()),
            'subject' => $event->message->getSubject(),
            'body_html' => $event->message->getHtmlBody(),
            'body_text' => $event->message->getTextBody(),
            'read' => false,
        ]);

        $this->prune();
    }

    private function getAddresses(array $addresses): array
    {
        return collect($addresses)->map(function ($address) {
            return [
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        })->toArray();
    }

    private function prune(): void
    {
        if ((int) config('mailweb.limit') === 0) {
            return;
        }

        MailwebEmail::oldest()->limit((int) config('mailweb.limit'))->delete();
    }
}
