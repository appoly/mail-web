<?php

namespace Appoly\MailWeb\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailwebSampleNotification extends Notification
{
    use Queueable;

    private $selectedTemplate;

    private $mails = [
        [
            'id' => 'contact_form',
            'subject' => 'New Contact Form Submission',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'You have received a new contact form submission from John Doe.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Contact Details:',
                ],
                [
                    'type' => 'line',
                    'content' => 'Email: john.doe@example.com',
                ],
                [
                    'type' => 'line',
                    'content' => 'Phone: +1 (555) 123-4567',
                ],
                [
                    'type' => 'line',
                    'content' => 'Message:',
                ],
                [
                    'type' => 'line',
                    'content' => "I'm interested in learning more about your premium services. Can you please provide me with pricing information and availability for a consultation next week?",
                ],
                [
                    'type' => 'button',
                    'content' => 'View in CRM',
                    'url' => 'http://example.com/crm/contacts/123',
                ],
                [
                    'type' => 'line',
                    'content' => 'This inquiry was received on March 7, 2025 at 10:15 AM.',
                ],
            ],
        ],
        [
            'id' => 'welcome',
            'subject' => 'Welcome to Our Platform 👋',
            'body' => [
                [
                    'type' => 'line',
                    'content' => "Hello Jane,",
                ],
                [
                    'type' => 'line',
                    'content' => "We're thrilled to welcome you to our platform! Your account has been successfully created and is ready to use.",
                ],
                [
                    'type' => 'line',
                    'content' => 'Here are a few things you can do to get started:',
                ],
                [
                    'type' => 'line',
                    'content' => '• Complete your profile to personalize your experience',
                ],
                [
                    'type' => 'line',
                    'content' => '• Explore our features and services',
                ],
                [
                    'type' => 'line',
                    'content' => '• Connect with other members in our community',
                ],
                [
                    'type' => 'button',
                    'content' => 'Get Started',
                    'url' => 'http://example.com/onboarding',
                ],
                [
                    'type' => 'line',
                    'content' => 'If you have any questions, our support team is here to help. Just reply to this email or visit our help center.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Best regards,',
                ],
                [
                    'type' => 'line',
                    'content' => 'The Example Team',
                ],
            ],
        ],
        [
            'id' => 'password_reset',
            'subject' => 'Reset Your Password 🔐',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'Hello,',
                ],
                [
                    'type' => 'line',
                    'content' => 'We received a request to reset your password. If you didn\'t make this request, you can safely ignore this email.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Your password reset link will expire in 60 minutes for security reasons.',
                ],
                [
                    'type' => 'button',
                    'content' => 'Reset Password',
                    'url' => 'http://example.com/reset-password?token=abc123',
                ],
                [
                    'type' => 'line',
                    'content' => 'If the button above doesn\'t work, you can copy and paste the following URL into your browser:',
                ],
                [
                    'type' => 'line',
                    'content' => 'http://example.com/reset-password?token=abc123',
                ],
                [
                    'type' => 'line',
                    'content' => 'For security, this request was received from IP: 192.168.1.1 using Chrome on Windows.',
                ],
                [
                    'type' => 'line',
                    'content' => 'If you didn\'t request a password reset, please contact our support team immediately.',
                ],
            ],
        ],
        [
            'id' => 'order_confirmation',
            'subject' => 'Order Confirmation #12345 ✅',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'Dear Customer,',
                ],
                [
                    'type' => 'line',
                    'content' => 'Thank you for your order! We\'re pleased to confirm that we\'ve received your order and it\'s being processed.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Order Details:',
                ],
                [
                    'type' => 'line',
                    'content' => 'Order Number: #12345',
                ],
                [
                    'type' => 'line',
                    'content' => 'Date: March 7, 2025',
                ],
                [
                    'type' => 'line',
                    'content' => 'Items:',
                ],
                [
                    'type' => 'line',
                    'content' => '• Product A - $49.99',
                ],
                [
                    'type' => 'line',
                    'content' => '• Product B - $29.99',
                ],
                [
                    'type' => 'line',
                    'content' => 'Subtotal: $79.98',
                ],
                [
                    'type' => 'line',
                    'content' => 'Shipping: $5.99',
                ],
                [
                    'type' => 'line',
                    'content' => 'Tax: $8.60',
                ],
                [
                    'type' => 'line',
                    'content' => 'Total: $94.57',
                ],
                [
                    'type' => 'button',
                    'content' => 'Track Your Order',
                    'url' => 'http://example.com/orders/12345',
                ],
                [
                    'type' => 'line',
                    'content' => 'Your order is expected to ship within 2 business days. You will receive another email with tracking information once your order ships.',
                ],
                [
                    'type' => 'line',
                    'content' => 'Thank you for shopping with us!',
                ],
            ],
        ],
        [
            'id' => 'event_invitation',
            'subject' => 'You\'re Invited: Virtual Conference 2025 🎟️',
            'body' => [
                [
                    'type' => 'line',
                    'content' => 'Hello,',
                ],
                [
                    'type' => 'line',
                    'content' => 'You\'re invited to attend our upcoming Virtual Conference 2025!',
                ],
                [
                    'type' => 'line',
                    'content' => 'Event Details:',
                ],
                [
                    'type' => 'line',
                    'content' => 'Date: April 15-17, 2025',
                ],
                [
                    'type' => 'line',
                    'content' => 'Time: 9:00 AM - 5:00 PM (EST)',
                ],
                [
                    'type' => 'line',
                    'content' => 'Location: Virtual (Zoom)',
                ],
                [
                    'type' => 'line',
                    'content' => 'Featured Speakers:',
                ],
                [
                    'type' => 'line',
                    'content' => '• Jane Smith - CEO, Innovation Labs',
                ],
                [
                    'type' => 'line',
                    'content' => '• John Johnson - CTO, Tech Solutions',
                ],
                [
                    'type' => 'line',
                    'content' => '• Sarah Williams - AI Research Director',
                ],
                [
                    'type' => 'button',
                    'content' => 'Register Now',
                    'url' => 'http://example.com/conference/register',
                ],
                [
                    'type' => 'line',
                    'content' => 'Early bird registration ends on March 31, 2025. Secure your spot today!',
                ],
                [
                    'type' => 'line',
                    'content' => 'We look forward to seeing you at the event.',
                ],
            ],
        ],
    ];

    /**
     * Create a new notification instance.
     * 
     * @param string|null $templateId Specific template ID to use, or null for random
     */
    public function __construct(string $templateId = null)
    {
        if ($templateId) {
            // Find the template with the matching ID
            foreach ($this->mails as $template) {
                if (isset($template['id']) && $template['id'] === $templateId) {
                    $this->selectedTemplate = $template;
                    break;
                }
            }
        }
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
        // Use selected template or pick a random one
        $mailContent = $this->selectedTemplate ?? $this->mails[array_rand($this->mails, 1)];

        $mail = (new MailMessage)
            ->subject($mailContent['subject'])
            ->greeting('Hello' . (isset($notifiable->name) ? ' ' . $notifiable->name : '') . '!');

        foreach ($mailContent['body'] as $body) {
            if ($body['type'] == 'line') {
                $mail = $mail->line($body['content']);
            } elseif ($body['type'] == 'button') {
                $mail = $mail->action($body['content'], $body['url']);
            }
        }

        // Add a sample attachment
        $mail->attachData('This is an example attachment content for testing purposes.', 'example.txt', [
            'mime' => 'text/plain'
        ]);

        // Add a footer
        $mail->salutation('Thank you for using our service!');

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
