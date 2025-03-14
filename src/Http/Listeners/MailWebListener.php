<?php

namespace Appoly\MailWeb\Http\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        DB::transaction(function () use ($event) {
            $mailwebEmail = MailwebEmail::create([
                'from' => $this->getAddresses($event->message->getFrom()),
                'to' => $this->getAddresses($event->message->getTo()),
                'cc' => $this->getAddresses($event->message->getCc()),
                'bcc' => $this->getAddresses($event->message->getBcc()),
                'subject' => $event->message->getSubject(),
                'body_html' => $event->message->getHtmlBody(),
                'body_text' => $event->message->getTextBody(),
                'read' => false,
            ]);

            foreach ($event->message->getAttachments() as $attachment) {
                if ($attachment instanceof \Symfony\Component\Mime\Part\DataPart) {
                    // Extract attachment details
                    $fileName = $attachment->getFilename();
                    $fileContent = $attachment->getBody();
                    $mimeType = $attachment->getMediaType() . '/' . $attachment->getMediaSubtype();
                    $fileSizeBytes = strlen($fileContent);

                    // Store the original name regardless of whether we end up backing up the file
                    $attachment = $mailwebEmail->attachments()->create([
                        'name' => $fileName,
                        'size_bytes' => $fileSizeBytes,
                        'path' => null,
                    ]);

                    try {
                        $storageDisk = config('MailWeb.MAILWEB_ATTACHMENTS.DISK');
                        // If the config is enabled, we store the file
                        if ($storageDisk) {
                            $path = config('MailWeb.MAILWEB_ATTACHMENTS.PATH') . '/' . $attachment->mailweb_email_id . '/' . $attachment->id;
                            Storage::disk($storageDisk)->put(
                                path: $path,
                                contents: $fileContent,
                                options: ['ContentType' => $mimeType]
                            );

                            $attachment->update([
                                'path' => $path,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // We don't want to fail the entire process, so just log the error and move on
                        report($e);

                        return;
                    }

                }
            }
        });
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
}
