<?php

namespace Appoly\MailWeb\Http\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\Part\DataPart;
use Illuminate\Mail\Events\MessageSending;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class MailWebListener
{
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

            foreach ($event->message->getAttachments() as $dataPart) {
                if ($dataPart instanceof DataPart) {
                    $fileName = $dataPart->getFilename();
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $fileContent = $dataPart->getBody();
                    $mimeType = $dataPart->getMediaType() . '/' . $dataPart->getMediaSubtype();
                    $fileSizeBytes = strlen($fileContent);

                    $attachmentRecord = $mailwebEmail->attachments()->create([
                        'name' => $fileName,
                        'size_bytes' => $fileSizeBytes,
                        'path' => null,
                    ]);

                    try {
                        $storageDisk = config('MailWeb.MAILWEB_ATTACHMENTS.DISK');

                        if ($storageDisk) {
                            $path = config('MailWeb.MAILWEB_ATTACHMENTS.PATH') . '/' . $attachmentRecord->mailweb_email_id . '/' . $attachmentRecord->id . '.' . $extension;
                            Storage::disk($storageDisk)->put(
                                path: $path,
                                contents: $fileContent,
                                options: ['ContentType' => $mimeType]
                            );

                            $attachmentRecord->update(['path' => $path]);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        });
    }

    private function getAddresses(array $addresses): array
    {
        return collect($addresses)->map(fn ($address) => [
            'address' => $address->getAddress(),
            'name' => $address->getName(),
        ])->toArray();
    }
}
