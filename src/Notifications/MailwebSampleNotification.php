<?php

namespace Appoly\MailWeb\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailwebSampleNotification extends Notification
{
    use Queueable;

    private $selectedTemplate;

    private $mails = null;

    public function __construct(?string $templateId = null)
    {
        $this->mails = [
            [
                'id' => 'contact_form',
                'subject' => 'New Contact Form Submission',
                'body' => [
                    ['type' => 'line', 'content' => 'A new contact form submission has been received from John Doe.'],
                    ['type' => 'line', 'content' => 'Details:'],
                    ['type' => 'line', 'content' => 'Email: john.doe@example.com'],
                    ['type' => 'line', 'content' => 'Phone: +1 (555) 123-4567'],
                    ['type' => 'line', 'content' => 'Message: "I’m interested in your premium services. Could you share pricing details and consultation availability for next week?"'],
                    ['type' => 'button', 'content' => 'View Full Message', 'url' => 'http://example.com/crm/contacts/123'],
                    ['type' => 'line', 'content' => 'Received: March 7, 2025 at 10:15 AM'],
                ],
            ],
            [
                'id' => 'welcome',
                'subject' => 'Welcome to the Platform',
                'body' => [
                    ['type' => 'line', 'content' => 'Hello Jane,'],
                    ['type' => 'line', 'content' => 'We’re pleased to welcome you to the platform. Your account is now active and ready for use.'],
                    ['type' => 'line', 'content' => 'To get started:'],
                    ['type' => 'line', 'content' => '• Update your profile to customize your experience'],
                    ['type' => 'line', 'content' => '• Explore the available features and tools'],
                    ['type' => 'line', 'content' => '• Connect with the developer community'],
                    ['type' => 'button', 'content' => 'Begin Setup', 'url' => 'http://example.com/onboarding'],
                    ['type' => 'line', 'content' => 'Need assistance? Reply to this email or visit our support portal.'],
                    ['type' => 'line', 'content' => 'Regards,'],
                ],
            ],
            [
                'id' => 'password_reset',
                'subject' => 'Password Reset Request',
                'body' => [
                    ['type' => 'line', 'content' => 'Hello,'],
                    ['type' => 'line', 'content' => 'A password reset request has been received for your account. If this wasn’t you, please disregard this email.'],
                    ['type' => 'line', 'content' => 'This link expires in 60 minutes for security purposes.'],
                    ['type' => 'button', 'content' => 'Reset Password', 'url' => 'http://example.com/reset-password?token=abc123'],
                    ['type' => 'line', 'content' => 'If the button doesn’t work, use this URL:'],
                    ['type' => 'line', 'content' => 'http://example.com/reset-password?token=abc123'],
                    ['type' => 'line', 'content' => 'Request origin: IP 192.168.1.1, Chrome on Windows'],
                    ['type' => 'line', 'content' => 'Suspicious activity? Contact support immediately.'],
                ],
            ],
            [
                'id' => 'order_confirmation',
                'subject' => 'Order Confirmation #12345',
                'body' => [
                    ['type' => 'line', 'content' => 'Dear Customer,'],
                    ['type' => 'line', 'content' => 'Thank you for your order. We’ve received Order #12345 and it’s now in processing.'],
                    ['type' => 'line', 'content' => 'Order Summary:'],
                    ['type' => 'line', 'content' => 'Order Number: #12345'],
                    ['type' => 'line', 'content' => 'Date: March 7, 2025'],
                    ['type' => 'line', 'content' => 'Items:'],
                    ['type' => 'line', 'content' => '• Plain Black T-Shirt - $49.99'],
                    ['type' => 'line', 'content' => '• Red T-Shirt - $29.99'],
                    ['type' => 'line', 'content' => 'Subtotal: $79.98'],
                    ['type' => 'line', 'content' => 'Shipping: $5.99'],
                    ['type' => 'line', 'content' => 'Tax: $8.60'],
                    ['type' => 'line', 'content' => 'Total: $94.57'],
                    ['type' => 'button', 'content' => 'Track Order', 'url' => 'http://example.com/orders/12345'],
                    ['type' => 'line', 'content' => 'Expected to ship within 2 business days. Tracking details will follow upon shipment.'],
                ],
            ],
            [
                'id' => 'event_invitation',
                'subject' => 'Invitation: Virtual Conference 2025',
                'body' => [
                    ['type' => 'line', 'content' => 'Hello,'],
                    ['type' => 'line', 'content' => 'You’re invited to join us at Virtual Conference ' . now()->addMonth(1)->format('Y') . ', a premier event for developers and tech professionals.'],
                    ['type' => 'line', 'content' => 'Event Information:'],
                    ['type' => 'line', 'content' => 'Date: ' . now()->addMonth(1)->format('F d, Y') . ' - ' . now()->addMonth(1)->addDays(2)->format('F d, Y')],
                    ['type' => 'line', 'content' => 'Time: 9:00 AM - 5:00 PM (EST)'],
                    ['type' => 'line', 'content' => 'Platform: Zoom'],
                    ['type' => 'line', 'content' => 'Speakers:'],
                    ['type' => 'line', 'content' => '• Jane Smith - CEO, Innovation Labs'],
                    ['type' => 'line', 'content' => '• John Johnson - CTO, Tech Solutions'],
                    ['type' => 'line', 'content' => '• Sarah Williams - AI Research Director'],
                    ['type' => 'button', 'content' => 'Register Now', 'url' => 'http://example.com/conference/register'],
                    ['type' => 'line', 'content' => 'Early registration deadline: ' . now()->addDays(10)->format('F d, Y') . '. Reserve your spot today.'],
                ],
            ],
        ];

        if ($templateId) {
            foreach ($this->mails as $template) {
                if (isset($template['id']) && $template['id'] === $templateId) {
                    $this->selectedTemplate = $template;
                    break;
                }
            }
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailContent = $this->selectedTemplate ?? $this->mails[array_rand($this->mails, 1)];

        $mail = (new MailMessage)
            ->subject($mailContent['subject'])
            ->greeting('Hello' . (isset($notifiable->name) ? ' ' . $notifiable->name : '') . ',');

        foreach ($mailContent['body'] as $body) {
            if ($body['type'] == 'line') {
                $mail->line($body['content']);
            } elseif ($body['type'] == 'button') {
                $mail->action($body['content'], $body['url']);
            }
        }

        // Add attachments based on template ID for testing variety
        switch ($mailContent['id']) {
            case 'contact_form':
                // Single attachment
                $mail->attachData('Contact form submission details for John Doe.', 'contact-details.txt', ['mime' => 'text/plain']);
                break;

            case 'welcome':
                $document = file_get_contents(__DIR__ . '/../../public/test-documents/onboarding-guide.pdf');
                $mail->attachData($document, 'onboarding-guide.pdf', ['mime' => 'application/pdf']);
                break;

            case 'password_reset':
                // No attachments
                break;

            case 'order_confirmation':
                $productImage = file_get_contents(__DIR__ . '/../../public/test-documents/product.jpg');
                $mail->attachData($productImage, 'product.jpg', ['mime' => 'image/jpeg']);
                $mail->attachData("order_id,product,price\n12345,Plain Black T-Shirt,$49.99\n12345,Red T-Shirt,$29.99", 'order-12345.csv', ['mime' => 'text/csv']);
                $mail->attachData('Order confirmation details for #12345.', 'confirmation.txt', ['mime' => 'text/plain']);
                break;

            case 'event_invitation':
                $document = file_get_contents(__DIR__ . '/../../public/test-documents/speaker-bios.docx');
                $mail->attachData($document, 'speaker-bios.docx', ['mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
                break;
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
