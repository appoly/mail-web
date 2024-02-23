<?php

namespace Appoly\MailWeb\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailwebSampleNotification extends Notification
{
    use Queueable;

    private array $mails = [
        [
            'subject' => 'New Contact Form Submission',
            'body' => ['You have received a new contact form submission:', 'Lorem ipsum dolor sit amet Consectetur adipiscing elit'],
        ],
        [
            'subject' => 'Welcome',
            'body' => ['Welcome to our site', 'Lorem ipsum dolor sit amet Consectetur adipiscing elit'],
        ],
        [
            'subject' => 'Thanks',
            'body' => ['Thank you for your message', 'Lorem ipsum dolor sit amet Consectetur adipiscing elit'],
        ],
    ];

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailContent = $this->mails[array_rand($this->mails, 1)];

        $mail = (new MailMessage)
            ->subject($mailContent['subject']);

        foreach ($mailContent['body'] as $body) {
            $mail = $mail->line($body);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
