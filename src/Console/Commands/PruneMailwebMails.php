<?php

namespace Appoly\MailWeb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class PruneMailwebMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailweb:prune {remaining?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old mailweb emails up to the limit passed, or the default set in config';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // If remaining is not provided, use the config default
        $remaining = $this->argument('remaining');
        if ($remaining === null) {
            $remaining = config('MailWeb.MAILWEB_LIMIT', 30);
        }

        $remaining = (int) $remaining;

        $emailIdsToDeleteKeyedById = MailwebEmail::latest()
            ->withCount(['attachments as hasFileAttachments' => fn($q) => $q->whereNotNull('path')])
            ->limit(5_000) // We need a limit to use offset, and to avoid memory issues. 5k is... probably enough, as this can just run more often if not
            ->offset($remaining) // We keep only the amount specified, so offset by that number
            ->pluck('hasFileAttachments', 'id') // Key of email ID, val of count of attachments
            ->toArray();

        $emailIdsWithAttachments = array_keys(array_filter($emailIdsToDeleteKeyedById, fn($count) => $count > 0));

        Log::info('Pruning ' . count($emailIdsToDeleteKeyedById) . ' emails, of which ' . count($emailIdsWithAttachments) . ' have attachments');

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
