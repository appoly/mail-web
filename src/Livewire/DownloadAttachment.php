<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Appoly\MailWeb\Http\Models\MailwebEmailAttachment;

class DownloadAttachment extends Component
{
    public MailwebEmailAttachment $attachment;
    public MailwebEmail $email;
    public ?string $errorMessage = null;

    public bool $isDownloading = false;

    // Initialize with the attachment ID and email ID
    public function mount($email, $attachment)
    {
        $this->attachment = $attachment;
        $this->email = $email;
    }

    public function render()
    {
        return view('mailweb::livewire.download-attachment');
    }

    // Handle the download request
    public function download()
    {
        $this->isDownloading = true;
        if ($this->attachment->mailweb_email_id !== $this->email->id) {
            $this->errorMessage = "Attachment does not belong to this email.";
            $this->isDownloading = false;
            return;
        }
        $this->errorMessage = "";

        try {
            // Fetch the attachment from the database, ensuring it belongs to the email
            if (!$this->attachment->path) {
                $this->errorMessage = 'The attachment does not have a path.';
                return;
            }

            $storageDisk = config('MailWeb.MAILWEB_ATTACHMENTS.DISK');

            if (!$storageDisk) {
                throw new \Exception('Storage disk not configured');
            }


            // Ensure the file exists
            if (!Storage::disk($storageDisk)->exists($this->attachment->path)) {
                $this->errorMessage = "The file could not be found.";
                return;
            }

            return Storage::disk($storageDisk)->download($this->attachment->path, $this->attachment->name);
        } catch (\Exception $e) {
            // Catch errors like attachment not found, etc.
            report($e);
            $this->errorMessage = "An error occurred while trying to download the attachment. Please check logs for more info.";
        } finally {
            $this->isDownloading = false;
        }
    }
}
