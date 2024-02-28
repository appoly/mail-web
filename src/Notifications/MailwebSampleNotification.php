<?php

namespace Appoly\MailWeb\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailwebSampleNotification extends Notification
{
    use Queueable;

    private $mails = [
        [
            'subject' => 'New Contact Form Submission',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'You have received a new contact form submission:',
                ],
                [
                    'type' => 'line',
                    'content' => 'Message:',
                ],
                [
                    'type' => 'line',
                    'content' => "I'm interested in learning more about your services. Can you please provide me with some additional information?",
                ],
            ],
        ],
        [
            'subject' => 'Welcome 👋🏻',
            'body' => [
                [
                    'type' => 'line',
                    'content' => "We're so excited to have you here! We think you'll love what we've got in store.",
                ],
                [
                    'type' => 'line',
                    'content' => 'To get started with your new account, please click the button below.',
                ],
                [
                    'type' => 'button',
                    'content' => 'Click here',
                    'url' => 'http://example.com',
                ],
                [
                    'type' => 'line',
                    'content' => 'We hope to see you soon!',
                ],
            ],
        ],
        [
            'subject' => 'Reset Password 🙈',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'Forgotten your password? No worries! We got you covered.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Click the button below to reset your password.',
                ],
                [
                    'type' => 'button',
                    'content' => 'Reset Password',
                    'url' => 'http://example.com',
                ],
                [
                    'type' => 'line',
                    'content' => 'If you did not request a password reset, please ignore this email.',
                ],
            ],
        ],
        [
            'subject' => 'New Comment on Your Post 📝',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'Someone has commented on your post.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Click the button below to view the comment.',
                ],
                [
                    'type' => 'button',
                    'content' => 'View Comment',
                    'url' => 'http://example.com',
                ],
            ],
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
            if ($body['type'] == 'line') {
                $mail = $mail->line($body['content']);
            } elseif ($body['type'] == 'button') {
                $mail = $mail->action($body['content'], $body['url']);
            }
        }

        // create a dummy text attachment
        $mail->attachData('Example Attachment', 'example.txt', ['mime' => 'text/plain']);

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
