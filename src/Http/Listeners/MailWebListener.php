<?php

namespace Appoly\MailWeb\Http\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Events\MessageSending;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Storage;

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
        if (!config('MailWeb.MAILWEB_ENABLED')) {
            return;
        }

        // DB::transaction(function () use ($event) {
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

                // Store the original name regardless of whether we end up backing up the file
                $attachment = $mailwebEmail->attachments()->create([
                    'name' => $fileName,
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
        $limit = (int) config('mailweb.limit'); // Should this be MailWeb.MAILWEB_LIMIT - go through this in separate ticket
        // $limit = (int) config('MailWeb.MAILWEB_LIMIT');

        if ($limit === 0) {
            // keep all - so do not prune
            return;
        }

        $emailIdsToDeleteKeyedById = MailwebEmail::latest()
            ->withCount(['attachments as hasFileAttachments' => fn($q) => $q->whereNotNull('path')])
            ->limit(5_000) // We need a limit to use offset, and to avoid memory issues. 1k is... probably enough, as this can just run more often if not?
            ->offset($limit) // We keep only the amount specified, so offset by that number
            ->pluck('hasFileAttachments', 'id') // Key of email ID, val of count of attachments
            ->toArray();

        $emailIdsWithAttachments = array_keys(array_filter($emailIdsToDeleteKeyedById, fn($count) => $count > 0));

        // Attachment cleanup needs to be done slowly
        if (count($emailIdsToDeleteKeyedById) > 0) {
            dispatch(function () use ($emailIdsWithAttachments) {
                // Chunk into batches of 100 for deletion
                $storageDisk = config('MailWeb.MAILWEB_ATTACHMENTS.DISK');
                if ($storageDisk) {
                    $basePath = config('MailWeb.MAILWEB_ATTACHMENTS.PATH');

                    foreach ($emailIdsWithAttachments as $emailId) {
                        Storage::disk($storageDisk)->deleteDirectory($basePath . '/' . $emailId);
                    }
                }
            });
        }

        MailwebEmail::whereIn('id', array_keys($emailIdsToDeleteKeyedById))->delete();

    }
}
